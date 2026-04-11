<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\Contrat;
use App\Models\NotificationImmogo;
use App\Models\Paiement;
use App\Services\FedaPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaiementController extends Controller
{
    public function __construct(private FedaPayService $fedaPay) {}

    // ── Résoudre le service FedaPay avec la clé de l'agence du bien ───────────
    private function fedaPayPour(?int $agenceId = null): FedaPayService
    {
        if ($agenceId) {
            $agence = \App\Models\Agence::find($agenceId);
            return new FedaPayService($agence);
        }
        return new FedaPayService();
    }

    // ── 1. Réservation (acompte 10%) ──────────────────────────────────────────
    // Appelé après le formulaire de réservation — lance FedaPay
    public function initReservation(Bien $bien)
    {
        abort_if($bien->statut !== 'disponible' || !$bien->is_published, 404);

        $pending = session('reservation_pending');
        if (!$pending || $pending['bien_id'] !== $bien->id) {
            return redirect()->route('client.reserver', $bien)
                ->withErrors(['error' => 'Session expirée, veuillez recommencer.']);
        }

        return $this->lancerFedaPay(
            montant:      $pending['montant'],
            description:  'Acompte réservation (10%) - ' . $bien->titre,
            typePaiement: 'acompte',
            meta: [
                'action'        => 'reservation',
                'bien_id'       => $bien->id,
                'agence_id'     => $bien->agence_id,
                'type_contrat'  => $pending['type_contrat'],
                'date_limite'   => $pending['date_limite'],
                'mode_paiement' => $pending['mode_paiement'],
            ]
        );
    }

    // ── 2. Paiement du solde (après réservation existante) ────────────────────
    public function showSolde(Contrat $contrat)
    {
        abort_if($contrat->client_id !== auth()->id(), 403);
        $montantAcompte = $contrat->getMontantAcompte();
        $montantPaye    = $contrat->getMontantPaye();
        $solde          = $contrat->getSoldeRestant();
        return view('client.paiement.solde', compact('contrat', 'montantAcompte', 'montantPaye', 'solde'));
    }

    public function payerSolde(Request $request, Contrat $contrat)
    {
        abort_if($contrat->client_id !== auth()->id(), 403);

        $data = $request->validate([
            'montant'       => 'required|numeric|min:1',
            'type_paiement' => 'required|in:acompte,solde',
        ]);

        return $this->lancerFedaPay(
            montant:      $data['montant'],
            description:  'Paiement ' . $data['type_paiement'] . ' - ' . $contrat->bien->titre,
            typePaiement: $data['type_paiement'],
            meta: [
                'action'     => 'solde',
                'contrat_id' => $contrat->id,
                'agence_id'  => $contrat->bien->agence_id,
            ]
        );
    }

    // ── 3. Paiement complet ───────────────────────────────────────────────────
    public function showComplet(Bien $bien)
    {
        abort_if($bien->statut !== 'disponible' || !$bien->is_published, 404);
        return view('client.paiement.complet', compact('bien'));
    }

    public function payerComplet(Request $request, Bien $bien)
    {
        abort_if($bien->statut !== 'disponible', 422);

        $data = $request->validate([
            'type_contrat' => 'required|in:location,vente',
        ]);

        return $this->lancerFedaPay(
            montant:      $bien->prix,
            description:  'Paiement complet - ' . $bien->titre,
            typePaiement: 'complet',
            meta: [
                'action'       => 'complet',
                'bien_id'      => $bien->id,
                'agence_id'    => $bien->agence_id,
                'type_contrat' => $data['type_contrat'],
            ]
        );
    }

    // ── Méthode commune : créer la transaction FedaPay ────────────────────────
    private function lancerFedaPay(float $montant, string $description, string $typePaiement, array $meta)
    {
        $user      = auth()->user();
        $reference = 'PAY-' . strtoupper(Str::random(10));

        session(['fedapay_pending' => array_merge($meta, [
            'reference'     => $reference,
            'montant'       => $montant,
            'type_paiement' => $typePaiement,
            'client_id'     => $user->id,
        ])]);

        // Utiliser la clé FedaPay de l'agence du bien
        $agenceId = $meta['agence_id'] ?? null;
        $fedaPay  = $this->fedaPayPour($agenceId);

        try {
            $result = $fedaPay->createTransaction([
                'amount'       => (int) $montant,
                'description'  => $description,
                'callback_url' => url('/paiement/callback'),
                'customer'     => [
                    'firstname'    => $user->prenom ?? $user->name,
                    'lastname'     => $user->name,
                    'email'        => $user->email,
                    'phone_number' => [
                        'number'  => preg_replace('/\D/', '', $user->telephone ?? '96000001'),
                        'country' => 'BJ',
                    ],
                ],
            ]);

            session(['fedapay_transaction_id' => $result['transaction_id']]);

            return redirect($result['payment_url']);

        } catch (\Exception $e) {
            return back()->withErrors(['fedapay' => 'Erreur de paiement : ' . $e->getMessage()]);
        }
    }

    // ── Callback FedaPay (appelé par FedaPay après paiement) ─────────────────
    public function callback(Request $request)
    {
        $transactionId = $request->input('id');
        if (!$transactionId) {
            return response()->json(['error' => 'ID manquant'], 400);
        }

        try {
            $statut = $this->fedaPay->verifyTransaction((int) $transactionId);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        if ($statut === 'approved') {
            $this->confirmerPaiement((int) $transactionId);
        }

        return response()->json(['status' => 'ok']);
    }

    // ── Page de retour après paiement FedaPay ─────────────────────────────────
    public function retour(Request $request)
    {
        $transactionId = $request->input('id') ?? session('fedapay_transaction_id');
        $status        = $request->input('status'); // FedaPay envoie le statut en GET

        // Si FedaPay dit declined/cancelled directement
        if (in_array($status, ['declined', 'cancelled'])) {
            session()->forget(['reservation_pending', 'fedapay_pending', 'fedapay_transaction_id']);
            return redirect()->route('home')
                ->with('error', 'Paiement annulé ou refusé. Aucune réservation n\'a été créée.');
        }

        if ($transactionId) {
            try {
                $statut = $this->fedaPay->verifyTransaction((int) $transactionId);

                if ($statut === 'approved') {
                    $paiement = $this->confirmerPaiement((int) $transactionId);
                    if ($paiement) {
                        return redirect()->route('client.historique')
                            ->with('success', 'Paiement confirmé ! Référence : ' . $paiement->reference);
                    }
                } elseif (in_array($statut, ['declined', 'cancelled'])) {
                    session()->forget(['reservation_pending', 'fedapay_pending', 'fedapay_transaction_id']);
                    return redirect()->route('home')
                        ->with('error', 'Paiement refusé. Aucune réservation n\'a été créée.');
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('FedaPay retour error: ' . $e->getMessage());
            }
        }

        return redirect()->route('client.historique')
            ->with('info', 'Votre paiement est en cours de traitement.');
    }

    // ── Confirmer le paiement et créer les entités ────────────────────────────
    private function confirmerPaiement(int $transactionId): ?Paiement
    {
        // Vérifier si déjà traité
        $existing = Paiement::where('fedapay_transaction_id', $transactionId)->first();
        if ($existing && $existing->statut === 'confirme') {
            return $existing;
        }

        $pending = session('fedapay_pending');
        if (!$pending) {
            return null;
        }

        $action = $pending['action'];

        if ($action === 'reservation') {
            return $this->creerReservation($pending, $transactionId);
        } elseif ($action === 'solde') {
            return $this->confirmerSolde($pending, $transactionId);
        } elseif ($action === 'complet') {
            return $this->creerPaiementComplet($pending, $transactionId);
        }

        return null;
    }

    private function creerReservation(array $pending, int $transactionId): Paiement
    {
        $bien = Bien::findOrFail($pending['bien_id']);

        // Créer le contrat maintenant que le paiement est confirmé
        $contrat = Contrat::create([
            'bien_id'                                    => $bien->id,
            'client_id'                                  => $pending['client_id'],
            'type_contrat'                               => $pending['type_contrat'],
            'statut_contrat'                             => 'en_attente',
            'date_contrat'                               => now(),
            'montant_total_' . $pending['type_contrat']  => $bien->prix,
            'date_reserv_' . $pending['type_contrat']    => now(),
            'date_limite_solde_' . $pending['type_contrat'] => $pending['date_limite'],
        ]);

        // Créer le paiement confirmé
        $paiement = Paiement::create([
            'contrat_id'             => $contrat->id,
            'client_id'              => $pending['client_id'],
            'montant'                => $pending['montant'],
            'date_paiement'          => now(),
            'type_paiement'          => 'acompte',
            'mode_paiement'          => $pending['mode_paiement'],
            'reference'              => $pending['reference'],
            'statut'                 => 'confirme',
            'fedapay_transaction_id' => $transactionId,
        ]);

        // Marquer le bien comme réservé → disparaît du site
        $bien->update(['statut' => 'reserve']);

        // Notification
        NotificationImmogo::create([
            'user_id' => $pending['client_id'],
            'titre'   => 'Réservation confirmée ✓',
            'message' => 'Votre réservation pour "' . $bien->titre . '" est confirmée. Acompte payé : ' . number_format($pending['montant'], 0, ',', ' ') . ' FCFA. Réf: ' . $pending['reference'],
            'lien'    => route('client.historique'),
        ]);

        session()->forget(['reservation_pending', 'fedapay_pending', 'fedapay_transaction_id']);

        return $paiement;
    }

    private function confirmerSolde(array $pending, int $transactionId): Paiement
    {
        $contrat = Contrat::findOrFail($pending['contrat_id']);

        $paiement = Paiement::create([
            'contrat_id'             => $contrat->id,
            'client_id'              => $pending['client_id'],
            'montant'                => $pending['montant'],
            'date_paiement'          => now(),
            'type_paiement'          => $pending['type_paiement'],
            'mode_paiement'          => 'mobile_money',
            'reference'              => $pending['reference'],
            'statut'                 => 'confirme',
            'fedapay_transaction_id' => $transactionId,
        ]);

        // Si solde totalement payé
        if ($contrat->getMontantPaye() >= $contrat->getMontantTotal()) {
            $contrat->update(['statut_contrat' => 'actif']);
            $contrat->bien->update([
                'statut' => $contrat->type_contrat === 'vente' ? 'vendu' : 'loue',
            ]);
        }

        NotificationImmogo::create([
            'user_id' => $pending['client_id'],
            'titre'   => 'Paiement confirmé ✓',
            'message' => 'Votre paiement de ' . number_format($pending['montant'], 0, ',', ' ') . ' FCFA a été confirmé. Réf: ' . $pending['reference'],
            'lien'    => route('client.historique'),
        ]);

        session()->forget(['fedapay_pending', 'fedapay_transaction_id']);

        return $paiement;
    }

    private function creerPaiementComplet(array $pending, int $transactionId): Paiement
    {
        $bien = Bien::findOrFail($pending['bien_id']);

        $contrat = Contrat::create([
            'bien_id'                                    => $bien->id,
            'client_id'                                  => $pending['client_id'],
            'type_contrat'                               => $pending['type_contrat'],
            'statut_contrat'                             => 'actif',
            'date_contrat'                               => now(),
            'montant_total_' . $pending['type_contrat']  => $bien->prix,
            'date_reserv_' . $pending['type_contrat']    => now(),
        ]);

        $paiement = Paiement::create([
            'contrat_id'             => $contrat->id,
            'client_id'              => $pending['client_id'],
            'montant'                => $bien->prix,
            'date_paiement'          => now(),
            'type_paiement'          => 'complet',
            'mode_paiement'          => 'mobile_money',
            'reference'              => $pending['reference'],
            'statut'                 => 'confirme',
            'fedapay_transaction_id' => $transactionId,
        ]);

        // Bien disparaît du site
        $bien->update([
            'statut' => $pending['type_contrat'] === 'vente' ? 'vendu' : 'loue',
        ]);

        NotificationImmogo::create([
            'user_id' => $pending['client_id'],
            'titre'   => 'Paiement complet confirmé ✓',
            'message' => 'Votre paiement complet pour "' . $bien->titre . '" est confirmé. Réf: ' . $pending['reference'],
            'lien'    => route('client.historique'),
        ]);

        session()->forget(['fedapay_pending', 'fedapay_transaction_id']);

        return $paiement;
    }
}
