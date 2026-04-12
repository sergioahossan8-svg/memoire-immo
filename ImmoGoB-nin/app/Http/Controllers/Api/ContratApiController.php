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
            'date_limite'   => 'required|date|after:today',
            'mode_paiement' => 'required|in:mobile_money,virement,especes,carte',
        ]);

        // Créer une notification pour la réservation
        \App\Models\NotificationImmogo::create([
            'user_id' => auth()->id(),
            'titre' => 'Réservation initiée',
            'message' => "Votre réservation pour le bien \"{$bien->titre}\" a été initiée. Veuillez procéder au paiement de l'acompte.",
            'lien' => "/biens/{$bien->id}",
            'lu' => false,
        ]);

        // Stocker dans le cache serveur (stateless — pas de session)
        // TTL 30 min : le client a 30 min pour confirmer le paiement
        $reservationKey = 'reservation_' . Str::random(24);
        Cache::put($reservationKey, [
            'bien_id'       => $bien->id,
            'type_contrat'  => $data['type_contrat'],
            'date_limite'   => $data['date_limite'],
            'mode_paiement' => $data['mode_paiement'],
            'montant'       => $bien->prix * 0.10,
            'type_paiement' => 'acompte',
        ], now()->addMinutes(30));

        return response()->json([
            'message'         => 'Réservation initiée. Procédez au paiement.',
            'bien_id'         => $bien->id,
            'montant_acompte' => $bien->prix * 0.10,
            'bien_titre'      => $bien->titre,
            'reservation_key' => $reservationKey,  // à conserver côté mobile pour initier le paiement
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
                'photo'       => $photo ? \Storage::url($photo->chemin) : null,
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
