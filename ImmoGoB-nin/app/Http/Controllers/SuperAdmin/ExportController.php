<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Agence;

class ExportController extends Controller
{
    public function agencesPdf()
    {
        $agences = Agence::with('adminPrincipal')->withCount('biens')->latest()->get();
        return view('superadmin.exports.agences-pdf', compact('agences'));
    }
}
