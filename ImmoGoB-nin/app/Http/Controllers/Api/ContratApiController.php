<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\Contrat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ContratApiController extends Controller
{
    public function historique()
    {
        $contrats = auth()->user()
            ->contrats()
            ->with(['bien.photos', 'bien.agence', 'paiements'])
            ->latest()
            ->get()
            ->map(fn($c) => $this->formatContrat($c));

        return response()->json(['contrats' => $contrats]);
    }

    public function show(Contrat $contrat)
    {
        if ($contrat->client_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $contrat->load(['bien.photos', 'bien.agence', 'paiements']);

        return response()->json(['contrat' => $this->formatContrat($contrat, true)]);
    }

    public function reserver(Request $request, Bien $bien)
    {
        if ($bien->statut !== 'disponible' || !$bien->is_published) {
            return response()->json(['message' => 'Ce bien n\'est plus disponible.'], 422);
        }

        $data = $request->validate([
            'type_contrat'  => 'required|in:location,vente',
            'date_limite'   => 'required|date|after:now',
            'mode_paiement' => 'required|in:mobile_money,virement,especes,carte',
        ]);

        // Verifier que le type_contrat correspond a la transaction du bien
        if ($data['type_contrat'] !== $bien->transaction) {
            return response()->json([
                'message' => 'Le type de contrat doit correspondre à la transaction du bien (' . $bien->transaction . ').'
            ], 422);
        }

        // Créer une notification pour la réservation
        \App\Models\NotificationImmogo::create([
            'user_id' => auth()->id(),
            'titre' => 'Réservation initiée',
            'message' => "Votre réservation pour le bien \"{$bien->titre}\" a été initiée. Veuillez procéder au paiement de l'acompte.",
            'lien' => "/biens/{$bien->id}",
            'lu' => false,
        ]);

        // Calculer le montant total selon le type de contrat
        // Pour une location : (prix × avance_mois) + caution_eau + caution_electricite
        // Pour une vente : prix du bien
        $montantTotal = $data['type_contrat'] === 'location'
            ? $bien->montant_total_location
            : (float) $bien->prix;

        $montantAcompte = $montantTotal * 0.10;

        // Stocker dans le cache serveur (stateless — pas de session)
        // TTL 30 min : le client a 30 min pour confirmer le paiement
        $reservationKey = 'reservation_' . Str::random(24);
        Cache::put($reservationKey, [
            'bien_id'       => $bien->id,
            'type_contrat'  => $data['type_contrat'],
            'date_limite'   => $data['date_limite'],
            'mode_paiement' => $data['mode_paiement'],
            'montant'       => $montantAcompte,
            'montant_total' => $montantTotal,
            'type_paiement' => 'acompte',
        ], now()->addMinutes(30));

        // CAS ESPÈCES : créer le contrat en_attente mais laisser le bien DISPONIBLE
        // L'admin confirmera le paiement manuellement → le bien passera à reserve/loue/vendu
        if ($data['mode_paiement'] === 'especes') {
            $contrat = \App\Models\Contrat::create([
                'bien_id'                                       => $bien->id,
                'client_id'                                     => auth()->id(),
                'type_contrat'                                  => $data['type_contrat'],
                'statut_contrat'                                => 'en_attente',
                'date_contrat'                                  => now(),
                'montant_total_' . $data['type_contrat']        => $montantTotal,
                'date_reserv_' . $data['type_contrat']          => now(),
                'date_limite_solde_' . $data['type_contrat']    => $data['date_limite'],
            ]);

            // Le bien reste DISPONIBLE jusqu'à confirmation du paiement par l'admin
            // $bien->update(['statut' => 'reserve']); ← volontairement absent

            $bien->load('agence.adminPrincipal');
            $agence = $bien->agence;

            \App\Models\NotificationImmogo::create([
                'user_id' => auth()->id(),
                'titre'   => 'Réservation en attente (Espèces)',
                'message' => 'Votre réservation pour "' . $bien->titre . '" est enregistrée. Rendez-vous au siège de l\'agence pour régler l\'acompte de ' . number_format($montantAcompte, 0, ',', ' ') . ' FCFA.',
                'lien'    => "/biens/{$bien->id}",
            ]);

            // Nettoyer le cache (pas nécessaire ici)
            Cache::forget($reservationKey);

            return response()->json([
                'message'           => 'Réservation enregistrée. Rendez-vous au siège de l\'agence.',
                'mode_paiement'     => 'especes',
                'contrat_id'        => $contrat->id,
                'bien_titre'        => $bien->titre,
                'montant_acompte'   => $montantAcompte,
                'agence' => $agence ? [
                    'nom'             => $agence->nom_commercial,
                    'telephone'       => $agence->telephone,
                    'email'           => $agence->email,
                    'adresse'         => $agence->adresse_complete,
                    'ville'           => $agence->ville,
                    'whatsapp'        => $agence->adminPrincipal?->whatsapp,
                ] : null,
            ]);
        }

        return response()->json([
            'message'         => 'Réservation initiée. Procédez au paiement.',
            'bien_id'         => $bien->id,
            'montant_total'   => $montantTotal,
            'montant_acompte' => $montantAcompte,
            'bien_titre'      => $bien->titre,
            'reservation_key' => $reservationKey,
        ]);
    }

    private function formatContrat(Contrat $contrat, bool $detail = false): array
    {
        $bien = $contrat->bien;
        $bienApi = new BienApiController();
        $photo = $bien?->photos?->first();

        $data = [
            'id'             => $contrat->id,
            'type_contrat'   => $contrat->type_contrat,
            'statut_contrat' => $contrat->statut_contrat,
            'date_contrat'   => $contrat->date_contrat?->format('d/m/Y'),
            'montant_total'  => $contrat->getMontantTotal(),
            'montant_acompte'=> $contrat->getMontantAcompte(),
            'montant_paye'   => $contrat->getMontantPaye(),
            'solde_restant'  => $contrat->getSoldeRestant(),
            'bien' => $bien ? [
                'id'          => $bien->id,
                'titre'       => $bien->titre,
                'localisation'=> $bien->localisation,
                'ville'       => $bien->ville,
                'transaction' => $bien->transaction,
                'prix'        => (float) $bien->prix,
                'photo'       => $photo
                    ? (str_starts_with($photo->chemin, 'http://') || str_starts_with($photo->chemin, 'https://')
                        ? $photo->chemin
                        : storage_url($photo->chemin))
                    : null,
                'agence'      => $bien->agence?->nom,
            ] : null,
        ];

        if ($detail) {
            $data['paiements'] = $contrat->paiements->map(fn($p) => [
                'id'            => $p->id,
                'montant'       => (float) $p->montant,
                'date_paiement' => $p->date_paiement?->format('d/m/Y H:i'),
                'type_paiement' => $p->type_paiement,
                'mode_paiement' => $p->mode_paiement,
                'reference'     => $p->reference,
                'statut'        => $p->statut,
            ]);
        }

        return $data;
    }
}
