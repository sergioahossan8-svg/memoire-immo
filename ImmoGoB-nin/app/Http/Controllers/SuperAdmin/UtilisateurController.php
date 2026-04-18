<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UtilisateurController extends Controller
{
    public function index()
    {
        $utilisateurs = User::with(['adminAgence.agence', 'superAdmin', 'client'])
            ->latest()
            ->paginate(30);
        return view('superadmin.utilisateurs', compact('utilisateurs'));
    }
}
