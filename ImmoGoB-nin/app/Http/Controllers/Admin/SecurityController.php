<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;

class SecurityController extends Controller
{
    public function index()
    {
        // Récupérer l'agence via la table spécialisée admin_agences
        $agenceId = auth()->user()->adminAgence?->agence_id;

        // Logs de l'agence : actions des admins de cette agence via admin_agences
        $adminIds = \App\Models\AdminAgence::where('agence_id', $agenceId)
            ->pluck('user_id');

        $logs = ActivityLog::whereIn('user_id', $adminIds)
            ->with('user')
            ->latest()
            ->paginate(30);

        return view('admin.security', compact('logs'));
    }
}
