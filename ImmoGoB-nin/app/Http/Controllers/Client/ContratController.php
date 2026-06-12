<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\Contrat;
use Illuminate\Http\Request;

class ContratController extends Controller
{
    public function historique()
    {
        $contrats = auth()->user()->contrats()
            ->with(['bien.photos', 'bien.agence', 'paiements'])
            ->latest()
            ->get()
            ->filter(fn($contrat) => $contrat->bien !== null)
            ->values();

        return view('client.historique', compact('contrats'));
    }

    // Page d'information après réservation en espèces
    public function showEspeces(Bien $bien, \App\Models\Contrat $contrat)
    {
        abort_if($contrat->client_id !== auth()->id(), 403);
        $bien->load(['agence.adminPrincipal']);
        $montantAcompte = $contrat->getMontantAcompte();
        return view('client.reservation_especes', compact('bien', 'contrat', 'montantAcompte'));
    }

    // Affiche le formulaire de réservation (acompte 10%)
    public function showReservation(Bien $bien)
    {
        abort_if($bien->statut !== 'disponible' || !$bien->is_published, 404);
        $bien->load(['agence', 'photos']);

        $montantTotal = $bien->transaction === 'location'
            ? $bien->montant_total_location
            : (float) $bien->prix;
        $acompte = $montantTotal * 0.10;

        return view('client.reservation', compact('bien', 'acompte', 'montantTotal'));
    }

    // Soumet le formulaire → KKiapay si paiement électronique, ou info agence si espèces
    public function reserver(Request $request, Bien $bien)
    {
        abort_if($bien->statut !== 'disponible' || !$bien->is_published, 422, 'Ce bien n\'est plus disponible.');

        $data = $request->validate([
            'type_contrat'  => 'required|in:location,vente',
            'mode_paiement' => 'required|in:mobile_money,virement,especes,carte',
        ]);

        $data['date_limite'] = now()->addDays(15)->format('Y-m-d');

        // Calculer le montant total selon les conditions de location
        $montantTotal = $bien->transaction === 'location'
            ? $bien->montant_total_location
            : (float) $bien->prix;
        $montantAcompte = $montantTotal * 0.10;

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

            \App\Models\NotificationImmogo::create([
                'user_id' => auth()->id(),
                'titre'   => 'Réservation en attente (Espèces)',
                'message' => 'Votre réservation pour "' . $bien->titre . '" est enregistrée. Rendez-vous au siège de l\'agence pour régler l\'acompte de ' . number_format($montantAcompte, 0, ',', ' ') . ' FCFA.',
                'lien'    => route('client.historique'),
            ]);

            return redirect()->route('client.reservation.especes', [
                'bien'    => $bien->id,
                'contrat' => $contrat->id,
            ]);
        }

        // CAS PAIEMENT ÉLECTRONIQUE : stocker en session → KKiapay
        session([
            'reservation_pending' => [
                'bien_id'       => $bien->id,
                'type_contrat'  => $data['type_contrat'],
                'date_limite'   => $data['date_limite'],
                'mode_paiement' => $data['mode_paiement'],
                'montant'       => $montantAcompte,
                'montant_total' => $montantTotal,
                'type_paiement' => 'acompte',
            ]
        ]);

        return redirect()->route('client.payer.reservation', $bien);
    }
}
