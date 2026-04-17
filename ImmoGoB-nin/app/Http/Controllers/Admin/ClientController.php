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
}
