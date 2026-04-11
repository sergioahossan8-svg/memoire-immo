<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bien;
use App\Models\Contrat;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $agence = auth()->user()->agence;

        $stats = [
            'biens_actifs' => Bien::where('agence_id', $agence->id)->where('is_published', true)->count(),
            'biens_en_vente' => Bien::where('agence_id', $agence->id)->where('transaction', 'vente')->count(),
            'biens_en_location' => Bien::where('agence_id', $agence->id)->where('transaction', 'location')->count(),
            'reservations' => Contrat::whereHas('bien', fn($q) => $q->where('agence_id', $agence->id))
                ->where('statut_contrat', 'en_attente')->count(),
        ];

        $biensRecents = Bien::where('agence_id', $agence->id)
            ->with(['photos', 'typeBien'])
            ->latest()->take(5)->get();

        $reservationsRecentes = Contrat::whereHas('bien', fn($q) => $q->where('agence_id', $agence->id))
            ->with(['bien', 'client'])
            ->latest()->take(5)->get();

        return view('admin.dashboard', compact('agence', 'stats', 'biensRecents', 'reservationsRecentes'));
    }
}
