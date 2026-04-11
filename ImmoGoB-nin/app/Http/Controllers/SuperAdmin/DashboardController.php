<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Bien;
use App\Models\Contrat;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'agences_actives'  => Agence::where('statut', 'actif')->count(),
            'administrateurs'  => User::where('role', 'admin_agence')->count(),
            'annonces_publiees'=> Bien::where('is_published', true)->count(),
            'clients'          => User::where('role', 'client')->count(),
        ];

        $agencesRecentes = Agence::with('adminPrincipal')->latest()->take(5)->get();

        return view('superadmin.dashboard', compact('stats', 'agencesRecentes'));
    }
}
