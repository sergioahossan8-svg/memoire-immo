<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->paginate(50);

        $actions = ActivityLog::distinct()->pluck('action')->sort()->values();
        $users   = \App\Models\User::whereIn('role', ['super_admin', 'admin_agence'])
            ->with('agence')->get();

        return view('superadmin.security', compact('logs', 'actions', 'users'));
    }
}
