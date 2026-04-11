<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;

class SecurityController extends Controller
{
    public function index()
    {
        $agenceId = auth()->user()->agence_id;

        // Logs de l'agence : actions des admins de cette agence
        $adminIds = \App\Models\User::where('agence_id', $agenceId)
            ->where('role', 'admin_agence')
            ->pluck('id');

        $logs = ActivityLog::whereIn('user_id', $adminIds)
            ->with('user')
            ->latest()
            ->paginate(30);

        return view('admin.security', compact('logs'));
    }
}
