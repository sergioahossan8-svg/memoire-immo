<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contrat;
use App\Models\User;

class ClientController extends Controller
{
    public function index()
    {
        // Récupérer l'agence_id via la table spécialisée admin_agences
        $agenceId = auth()->user()->adminAgence?->agence_id;

        $clients = User::where('role', 'client')
            ->whereHas('contrats.bien', fn($q) => $q->where('agence_id', $agenceId))
            ->withCount('contrats')
            ->paginate(20);

        return view('admin.clients.index', compact('clients'));
    }

    public function show(User $user)
    {
        $agenceId = auth()->user()->adminAgence?->agence_id;
        $contrats = $user->contrats()
            ->whereHas('bien', fn($q) => $q->where('agence_id', $agenceId))
            ->with(['bien.photos', 'paiements'])
            ->get();

        return view('admin.clients.show', compact('user', 'contrats'));
    }

    public function reservations()
    {
        $agenceId = auth()->user()->adminAgence?->agence_id;
        $reservations = Contrat::whereHas('bien', fn($q) => $q->where('agence_id', $agenceId))
            ->with(['bien.photos', 'client', 'paiements'])
            ->latest()
            ->paginate(20);

        return view('admin.reservations', compact('reservations'));
    }

    /**
     * Confirmer qu'un client a payé en espèces.
     * L'admin coche "Payé en espèces" → le paiement est enregistré,
     * le contrat passe à "actif" et le bien passe à "réservé" (acompte) ou "loué/vendu" (complet).
     */
    public function confirmerEspeces(\App\Models\Contrat $contrat)
    {
        $agenceId = auth()->user()->adminAgence?->agence_id;

        // Sécurité : le contrat appartient bien à l'agence
        abort_if($contrat->bien?->agence_id !== $agenceId, 403);
        abort_if($contrat->statut_contrat !== 'en_attente', 422, 'Ce contrat n\'est plus en attente.');

        $bien         = $contrat->bien;
        $montantTotal = $contrat->getMontantTotal();
        $acompte      = $montantTotal * 0.10;
        $reference    = 'ESP-' . strtoupper(\Illuminate\Support\Str::random(10));

        // Enregistrer le paiement de l'acompte en espèces
        \App\Models\Paiement::create([
            'contrat_id'    => $contrat->id,
            'client_id'     => $contrat->client_id,
            'montant'       => $acompte,
            'date_paiement' => now(),
            'type_paiement' => 'acompte',
            'mode_paiement' => 'especes',
            'reference'     => $reference,
            'statut'        => 'confirme',
        ]);

        // Contrat → actif (acompte payé, solde à régler), bien → réservé
        $contrat->update(['statut_contrat' => 'actif']);
        $bien->update(['statut' => 'reserve']);

        // Notifier le client
        \App\Models\NotificationImmogo::create([
            'user_id' => $contrat->client_id,
            'titre'   => 'Acompte espèces confirmé ✓',
            'message' => 'Votre acompte de ' . number_format($acompte, 0, ',', ' ') . ' FCFA pour "' . $bien->titre . '" a été confirmé par l\'agence. Réf : ' . $reference,
            'lien'    => route('client.historique'),
        ]);

        \App\Models\ActivityLog::log('especes_confirme', 'Acompte espèces confirmé pour contrat #' . $contrat->id . ' — bien : ' . $bien->titre, $contrat);

        return back()->with('success', 'Paiement en espèces confirmé. Le bien est maintenant réservé.');
    }
}
