<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bien;

class ExportController extends Controller
{
    public function biensPdf()
    {
        $agence = auth()->user()->agence;

        $biens = Bien::where('agence_id', $agence->id)
            ->with(['typeBien'])
            ->latest()
            ->get();

        // Contrats avec clients (réservations, locations, ventes)
        $contrats = \App\Models\Contrat::whereHas('bien', fn($q) => $q->where('agence_id', $agence->id))
            ->whereIn('statut_contrat', ['en_attente', 'actif'])
            ->with(['bien.typeBien', 'client', 'paiements'])
            ->latest()
            ->get();

        return view('admin.exports.biens-pdf', compact('biens', 'agence', 'contrats'));
    }
}
