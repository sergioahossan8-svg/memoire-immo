<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\Contrat;
use App\Models\NotificationImmogo;
use App\Models\Paiement;
use App\Services\KKiapayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PaiementApiController extends Controller
{
    /**
     * Initier un paiement complet (100%) via KKiapay
     * Retourne la clé publique KKiapay pour que le mobile ouvre le widget
     */
    public function payerComplet(Request $request, Bien $bien)
    {
        if ($bien->statut !== 'disponible' || !$bien->is_published) {
            return response()->json(['message' => 'Ce bien n\'est plus disponible.'], 422);
        }

        $data = $request->validate([
            'type_contrat' => 'required|in:location,vente',
        ]);

        return $this->initierKkiapay(
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
     * Initier le paiement du solde d'un contrat existant
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

        return $this->initierKkiapay(
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
     * Initier le paiement de l'acompte après réservation
     * Le mobile envoie la reservation_key reçue lors de l'étape reserver()
     */
    public function initReservation(Request $request, Bien $bien)
    {
        if ($bien->statut !== 'disponible' || !$bien->is_published) {
            return response()->json(['message' => 'Ce bien n\'est plus disponible.'], 422);
        }

        $reservationKey = $request->input('reservation_key');
        $pending        = $reservationKey ? Cache::get($reservationKey) : null;

        if (!$pending || $pending['bien_id'] !== $bien->id) {
            return response()->json(['message' => 'Réservation introuvable ou expirée. Recommencez la réservation.'], 422);
        }

        return $this->initierKkiapay(
            montant:      $pending['montant'],
            description:  'Acompte réservation (10%) - ' . $bien->titre,
            typePaiement: 'acompte',
            meta: [
                'action'          => 'reservation',
                'bien_id'         => $bien->id,
                'agence_id'       => $bien->agence_id,
                'type_contrat'    => $pending['type_contrat'],
                'date_limite'     => $pending['date_limite'],
                'mode_paiement'   => $pending['mode_paiement'],
                'reservation_key' => $reservationKey,
            ]
        );
    }

    /**
     * Préparer les données KKiapay et stocker le pending dans le cache (stateless)
     * Le mobile utilise la clé publique pour ouvrir le widget KKiapay
     */
    private function initierKkiapay(float $montant, string $description, string $typePaiement, array $meta)
    {
        $user       = auth()->user();
        $reference  = 'PAY-' . strtoupper(Str::random(10));
        $pendingKey = 'pay_' . Str::random(24);

        $pendingData = array_merge($meta, [
            'reference'     => $reference,
            'montant'       => $montant,
            'type_paiement' => $typePaiement,
            'client_id'     => $user->id,
        ]);

        // Stocker dans le cache (TTL 30 min) — pas de session, compatible API stateless
        Cache::put($pendingKey, $pendingData, now()->addMinutes(30));

        // Récupérer les clés KKiapay de l'agence
        $agenceId = $meta['agence_id'] ?? null;
        $agence   = $agenceId ? \App\Models\Agence::find($agenceId) : null;
        $kkiapay  = new KKiapayService($agence);

        return response()->json([
            'kkiapay_public_key' => $kkiapay->getPublicKey(),
            'kkiapay_sandbox'    => $kkiapay->isSandbox(),
            'montant'            => (int) $montant,
            'description'        => $description,
            'reference'          => $reference,
            'pending_key'        => $pendingKey,
            'client_email'       => $user->email,
            'client_phone'       => preg_replace('/\D/', '', $user->telephone ?? ''),
            'client_nom'         => trim(($user->prenom ?? '') . ' ' . $user->name),
        ]);
    }

    /**
     * Confirmer le paiement après succès du widget KKiapay (appelé par le mobile)
     */
    public function confirmerKkiapay(Request $request)
    {
        $transactionId = $request->input('transaction_id');
        $pendingKey    = $request->input('pending_key');
        $pending       = $pendingKey ? Cache::get($pendingKey) : null;

        if (!$pending || !$transactionId) {
            return response()->json(['message' => 'Données de paiement introuvables ou expirées.'], 422);
        }

        // Vérifier la transaction côté serveur avec les clés de l'agence
        $agenceId = $pending['agence_id'] ?? null;
        $agence   = $agenceId ? \App\Models\Agence::find($agenceId) : null;
        $kkiapay  = new KKiapayService($agence);

        if (!$kkiapay->isApproved($transactionId)) {
            return response()->json(['message' => 'Paiement non confirmé par KKiapay.'], 422);
        }

        // Traiter le paiement
        $paiement = $this->traiterPaiement($pending, (int) $transactionId);

        // Nettoyer le cache
        Cache::forget($pendingKey);
        if (!empty($pending['reservation_key'])) {
            Cache::forget($pending['reservation_key']);
        }

        if ($paiement) {
            return response()->json([
                'message'   => 'Paiement confirmé avec succès !',
                'reference' => $paiement->reference,
                'statut'    => 'confirme',
            ]);
        }

        return response()->json(['message' => 'Paiement traité.', 'statut' => 'traite']);
    }

    /**
     * Callback webhook (optionnel — KKiapay peut appeler cette URL)
     */
    public function callback(Request $request)
    {
        return response()->json(['status' => 'ok']);
    }

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
            'lien'    => '/client/historique',
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
            'lien'    => '/client/historique',
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
            'lien'    => '/client/historique',
        ]);

        return $paiement;
    }
}
