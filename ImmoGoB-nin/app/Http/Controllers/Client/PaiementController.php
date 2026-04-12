<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\Contrat;
use App\Models\NotificationImmogo;
use App\Models\Paiement;
use App\Services\KKiapayService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaiementController extends Controller
{
    // ── 1. Réservation (acompte 10%) ──────────────────────────────────────────
    public function initReservation(Bien $bien)
    {
        abort_if($bien->statut !== 'disponible' || !$bien->is_published, 404);

        $pending = session('reservation_pending');
        if (!$pending || $pending['bien_id'] !== $bien->id) {
            return redirect()->route('client.reserver', $bien)
                ->withErrors(['error' => 'Session expirée, veuillez recommencer.']);
        }

        return $this->lancerPaiement(
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

    // ── 2. Paiement du solde ──────────────────────────────────────────────────
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

        return $this->lancerPaiement(
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

        $data = $request->validate(['type_contrat' => 'required|in:location,vente']);

        return $this->lancerPaiement(
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

    // ── Méthode commune : stocker en session et rediriger vers KKiapay ────────
    private function lancerPaiement(float $montant, string $description, string $typePaiement, array $meta)
    {
        $user       = auth()->user();
        $reference  = 'PAY-' . strtoupper(Str::random(10));
        $pendingKey = 'pay_' . Str::random(20);

        session([
            $pendingKey       => array_merge($meta, [
                'reference'     => $reference,
                'montant'       => $montant,
                'type_paiement' => $typePaiement,
                'client_id'     => $user->id,
            ]),
            'pay_pending_key' => $pendingKey,
            'pay_montant'     => $montant,
            'pay_description' => $description,
            'pay_agence_id'   => $meta['agence_id'] ?? null,
        ]);

        return redirect()->route('paiement.kkiapay');
    }

    // ── KKiapay — afficher la page de paiement ────────────────────────────────
    public function showKkiapay()
    {
        $pendingKey  = session('pay_pending_key');
        $montant     = session('pay_montant');
        $description = session('pay_description');
        $agenceId    = session('pay_agence_id');

        if (!$pendingKey || !$montant) {
            return redirect()->route('home')->with('error', 'Session expirée, veuillez recommencer.');
        }

        $agence = $agenceId ? \App\Models\Agence::find($agenceId) : null;
        $kkiapay = new KKiapayService($agence);

        $kkiapayPublicKey = $kkiapay->getPublicKey();
        $kkiapaySandbox   = $kkiapay->isSandbox();

        return view('client.paiement.kkiapay', compact(
            'montant', 'description', 'pendingKey', 'kkiapayPublicKey', 'kkiapaySandbox'
        ));
    }

    // ── KKiapay — confirmer après succès du widget ────────────────────────────
    public function confirmerKkiapay(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $pendingKey    = $request->input('pending_key');
        $pending       = session($pendingKey);

        if (!$pending || !$transactionId) {
            return redirect()->route('home')->with('error', 'Session expirée.');
        }

        $agenceId = $pending['agence_id'] ?? null;
        $agence   = $agenceId ? \App\Models\Agence::find($agenceId) : null;
        $kkiapay  = new KKiapayService($agence);

        if (!$kkiapay->isApproved($transactionId)) {
            return redirect()->route('home')
                ->with('error', 'Paiement non confirmé. Veuillez contacter le support.');
        }

        $paiement = $this->traiterPaiement($pending, (int) $transactionId);

        session()->forget([$pendingKey, 'pay_pending_key', 'pay_montant', 'pay_description', 'pay_agence_id']);

        if ($paiement) {
            return redirect()->route('client.historique')
                ->with('success', 'Paiement confirmé ! Référence : ' . $paiement->reference);
        }

        return redirect()->route('client.historique')->with('info', 'Paiement traité.');
    }

    // ── Traiter le paiement selon l'action ────────────────────────────────────
    private function traiterPaiement(array $pending, int $transactionId): ?Paiement
    {
        return match($pending['action']) {
            'reservation' => $this->creerReservation($pending, $transactionId),
            'solde'       => $this->confirmerSolde($pending, $transactionId),
            'complet'     => $this->creerPaiementComplet($pending, $transactionId),
            default       => null,
        };
    }

    private function creerReservation(array $pending, int $transactionId): Paiement
    {
        $bien = Bien::findOrFail($pending['bien_id']);

        $contrat = Contrat::create([
            'bien_id'                                       => $bien->id,
            'client_id'                                     => $pending['client_id'],
            'type_contrat'                                  => $pending['type_contrat'],
            'statut_contrat'                                => 'en_attente',
            'date_contrat'                                  => now(),
            'montant_total_' . $pending['type_contrat']     => $bien->prix,
            'date_reserv_' . $pending['type_contrat']       => now(),
            'date_limite_solde_' . $pending['type_contrat'] => $pending['date_limite'],
        ]);

        $paiement = Paiement::create([
            'contrat_id'             => $contrat->id,
            'client_id'              => $pending['client_id'],
            'montant'                => $pending['montant'],
            'date_paiement'          => now(),
            'type_paiement'          => 'acompte',
            'mode_paiement'          => $pending['mode_paiement'] ?? 'mobile_money',
            'reference'              => $pending['reference'],
            'statut'                 => 'confirme',
            'kkiapay_transaction_id' => $transactionId,
        ]);

        $bien->update(['statut' => 'reserve']);

        NotificationImmogo::create([
            'user_id' => $pending['client_id'],
            'titre'   => 'Réservation confirmée ✓',
            'message' => 'Votre réservation pour "' . $bien->titre . '" est confirmée. Acompte : ' . number_format($pending['montant'], 0, ',', ' ') . ' FCFA. Réf: ' . $pending['reference'],
            'lien'    => route('client.historique'),
        ]);

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
            'kkiapay_transaction_id' => $transactionId,
        ]);

        if ($contrat->getMontantPaye() >= $contrat->getMontantTotal()) {
            $contrat->update(['statut_contrat' => 'actif']);
            $contrat->bien->update(['statut' => $contrat->type_contrat === 'vente' ? 'vendu' : 'loue']);
        }

        NotificationImmogo::create([
            'user_id' => $pending['client_id'],
            'titre'   => 'Paiement confirmé ✓',
            'message' => 'Paiement de ' . number_format($pending['montant'], 0, ',', ' ') . ' FCFA confirmé. Réf: ' . $pending['reference'],
            'lien'    => route('client.historique'),
        ]);

        return $paiement;
    }

    private function creerPaiementComplet(array $pending, int $transactionId): Paiement
    {
        $bien = Bien::findOrFail($pending['bien_id']);

        $contrat = Contrat::create([
            'bien_id'                                   => $bien->id,
            'client_id'                                 => $pending['client_id'],
            'type_contrat'                              => $pending['type_contrat'],
            'statut_contrat'                            => 'actif',
            'date_contrat'                              => now(),
            'montant_total_' . $pending['type_contrat'] => $bien->prix,
            'date_reserv_' . $pending['type_contrat']   => now(),
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
            'kkiapay_transaction_id' => $transactionId,
        ]);

        $bien->update(['statut' => $pending['type_contrat'] === 'vente' ? 'vendu' : 'loue']);

        NotificationImmogo::create([
            'user_id' => $pending['client_id'],
            'titre'   => 'Paiement complet confirmé ✓',
            'message' => 'Paiement complet pour "' . $bien->titre . '" confirmé. Réf: ' . $pending['reference'],
            'lien'    => route('client.historique'),
        ]);

        return $paiement;
    }

    // ── Callback (compatibilité) ──────────────────────────────────────────────
    public function callback(Request $request)
    {
        return response()->json(['status' => 'ok']);
    }

    public function retour(Request $request)
    {
        return redirect()->route('client.historique');
    }
}
