<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\Contrat;
use App\Models\NotificationImmogo;
use App\Models\Paiement;
use App\Services\FedaPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaiementApiController extends Controller
{
    public function __construct(private FedaPayService $fedaPay) {}

    /**
     * Paiement complet (100%) via FedaPay — retourne l'URL de paiement
     */
    public function payerComplet(Request $request, Bien $bien)
    {
        if ($bien->statut !== 'disponible' || !$bien->is_published) {
            return response()->json(['message' => 'Ce bien n\'est plus disponible.'], 422);
        }

        $data = $request->validate([
            'type_contrat' => 'required|in:location,vente',
        ]);

        return $this->lancerFedaPayApi(
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

    /**
     * Paiement solde d'un contrat existant
     */
    public function payerSolde(Request $request, Contrat $contrat)
    {
        if ($contrat->client_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $data = $request->validate([
            'montant'       => 'required|numeric|min:1',
            'type_paiement' => 'required|in:acompte,solde',
        ]);

        return $this->lancerFedaPayApi(
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

    /**
     * Initier FedaPay après réservation (acompte 10%)
     */
    public function initReservation(Bien $bien)
    {
        if ($bien->statut !== 'disponible' || !$bien->is_published) {
            return response()->json(['message' => 'Ce bien n\'est plus disponible.'], 422);
        }

        $pending = session('reservation_pending');
        if (!$pending || $pending['bien_id'] !== $bien->id) {
            return response()->json(['message' => 'Session expirée. Recommencez la réservation.'], 422);
        }

        return $this->lancerFedaPayApi(
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

    /**
     * Créer transaction FedaPay et retourner l'URL + transaction_id
     */
    private function lancerFedaPayApi(float $montant, string $description, string $typePaiement, array $meta)
    {
        $user      = auth()->user();
        $reference = 'PAY-' . strtoupper(Str::random(10));

        session(['fedapay_pending' => array_merge($meta, [
            'reference'     => $reference,
            'montant'       => $montant,
            'type_paiement' => $typePaiement,
            'client_id'     => $user->id,
        ])]);

        $agenceId = $meta['agence_id'] ?? null;
        $fedaPay  = $agenceId
            ? new FedaPayService(\App\Models\Agence::find($agenceId))
            : $this->fedaPay;

        // callback_url pour le mobile — FedaPay redirigera vers l'app via deep link
        $callbackUrl = url('/api/paiement/callback');

        try {
            $result = $fedaPay->createTransaction([
                'amount'       => (int) $montant,
                'description'  => $description,
                'callback_url' => $callbackUrl,
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

            return response()->json([
                'payment_url'    => $result['payment_url'],
                'transaction_id' => $result['transaction_id'],
                'montant'        => $montant,
                'reference'      => $reference,
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur de paiement : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Callback FedaPay (webhook POST)
     */
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

    /**
     * Vérifier le statut d'une transaction (polling depuis le mobile)
     */
    public function verifierTransaction(Request $request)
    {
        $transactionId = $request->input('transaction_id')
            ?? session('fedapay_transaction_id');

        if (!$transactionId) {
            return response()->json(['statut' => 'inconnu']);
        }

        try {
            $statut = $this->fedaPay->verifyTransaction((int) $transactionId);

            if ($statut === 'approved') {
                $paiement = $this->confirmerPaiement((int) $transactionId);
                return response()->json([
                    'statut'    => 'approved',
                    'reference' => $paiement?->reference,
                ]);
            }

            return response()->json(['statut' => $statut]);
        } catch (\Exception $e) {
            return response()->json(['statut' => 'error', 'message' => $e->getMessage()]);
        }
    }

    private function confirmerPaiement(int $transactionId): ?Paiement
    {
        $existing = Paiement::where('fedapay_transaction_id', $transactionId)->first();
        if ($existing && $existing->statut === 'confirme') {
            return $existing;
        }

        $pending = session('fedapay_pending');
        if (!$pending) return null;

        $action = $pending['action'];

        return match ($action) {
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
            'bien_id'                                    => $bien->id,
            'client_id'                                  => $pending['client_id'],
            'type_contrat'                               => $pending['type_contrat'],
            'statut_contrat'                             => 'en_attente',
            'date_contrat'                               => now(),
            'montant_total_' . $pending['type_contrat']  => $bien->prix,
            'date_reserv_' . $pending['type_contrat']    => now(),
            'date_limite_solde_' . $pending['type_contrat'] => $pending['date_limite'],
        ]);

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

        $bien->update(['statut' => 'reserve']);

        NotificationImmogo::create([
            'user_id' => $pending['client_id'],
            'titre'   => 'Réservation confirmée ✓',
            'message' => 'Votre réservation pour "' . $bien->titre . '" est confirmée. Acompte payé : ' . number_format($pending['montant'], 0, ',', ' ') . ' FCFA. Réf: ' . $pending['reference'],
            'lien'    => '/client/historique',
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
            'lien'    => '/client/historique',
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

        $bien->update([
            'statut' => $pending['type_contrat'] === 'vente' ? 'vendu' : 'loue',
        ]);

        NotificationImmogo::create([
            'user_id' => $pending['client_id'],
            'titre'   => 'Paiement complet confirmé ✓',
            'message' => 'Votre paiement complet pour "' . $bien->titre . '" est confirmé. Réf: ' . $pending['reference'],
            'lien'    => '/client/historique',
        ]);

        session()->forget(['fedapay_pending', 'fedapay_transaction_id']);

        return $paiement;
    }
}
