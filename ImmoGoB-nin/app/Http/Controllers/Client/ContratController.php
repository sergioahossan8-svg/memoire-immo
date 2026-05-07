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

    // Affiche le formulaire de réservation (acompte 10%)
    public function showReservation(Bien $bien)
    {
        abort_if($bien->statut !== 'disponible' || !$bien->is_published, 404);
        $acompte = $bien->prix * 0.10;
        return view('client.reservation', compact('bien', 'acompte'));
    }

    // Soumet le formulaire → va directement vers KKiapay SANS créer le contrat
    public function reserver(Request $request, Bien $bien)
    {
        abort_if($bien->statut !== 'disponible' || !$bien->is_published, 422, 'Ce bien n\'est plus disponible.');

        // La date limite est fixée à 15 jours (envoyée depuis le formulaire en champ caché)
        // On ignore la valeur soumise et on calcule côté serveur pour éviter toute manipulation
        $data = $request->validate([
            'type_contrat'  => 'required|in:location,vente',
            'mode_paiement' => 'required|in:mobile_money,virement,especes,carte',
        ]);

        $data['date_limite'] = now()->addDays(15)->format('Y-m-d');

        // Stocker les infos en session — le contrat sera créé APRÈS confirmation KKiapay
        session([
            'reservation_pending' => [
                'bien_id'       => $bien->id,
                'type_contrat'  => $data['type_contrat'],
                'date_limite'   => $data['date_limite'],
                'mode_paiement' => $data['mode_paiement'],
                'montant'       => $bien->prix * 0.10,
                'type_paiement' => 'acompte',
            ]
        ]);

        // Rediriger vers KKiapay via PaiementController
        return redirect()->route('client.payer.reservation', $bien);
    }
}
