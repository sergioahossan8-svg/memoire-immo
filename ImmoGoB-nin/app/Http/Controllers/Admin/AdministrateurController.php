<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAgence;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdministrateurController extends Controller
{
    public function index()
    {
        // Récupérer l'agence via la table spécialisée admin_agences
        $adminAgence = auth()->user()->adminAgence;
        $agence = $adminAgence->agence;

        $admins = AdminAgence::where('agence_id', $agence->id)
            ->with('user')
            ->get();

        return view('admin.administrateurs.index', compact('admins', 'agence'));
    }

    public function create()
    {
        return view('admin.administrateurs.create');
    }

    public function store(Request $request)
    {
        // Seul l'admin principal peut créer d'autres admins
        $currentAdminAgence = auth()->user()->adminAgence;
        abort_if(!$currentAdminAgence || !$currentAdminAgence->est_principal, 403, 'Seul l\'administrateur principal peut créer des admins.');

        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'email'     => 'required|email|unique:users',
            'telephone' => 'nullable|string|max:20',
            'whatsapp'  => 'nullable|string|max:20',
            'password'  => 'required|min:8|confirmed',
        ]);

        // 1. Créer dans la table mère users (données communes)
        $user = User::create([
            'name'      => $data['name'],
            'prenom'    => $data['prenom'],
            'email'     => $data['email'],
            'telephone' => $data['telephone'] ?? null,
            'role'      => 'admin_agence',
            'password'  => Hash::make($data['password']),
        ]);

        // 2. Créer dans la table spécialisée admin_agences (données spécifiques)
        AdminAgence::create([
            'user_id'       => $user->id,
            'agence_id'     => $currentAdminAgence->agence_id,
            'est_principal' => false,
            'whatsapp'      => $data['whatsapp'] ?? null,
        ]);

        $user->assignRole('admin_agence');

        return redirect()->route('admin.administrateurs.index')->with('success', 'Administrateur créé.');
    }

    public function edit(User $administrateur)
    {
        $adminAgence        = auth()->user()->adminAgence;
        $cibleAdminAgence   = $administrateur->adminAgence;
        abort_if($cibleAdminAgence?->agence_id !== $adminAgence?->agence_id, 403);
        return view('admin.administrateurs.edit', compact('administrateur', 'cibleAdminAgence'));
    }

    public function update(Request $request, User $administrateur)
    {
        $adminAgence      = auth()->user()->adminAgence;
        $cibleAdminAgence = $administrateur->adminAgence;
        abort_if($cibleAdminAgence?->agence_id !== $adminAgence?->agence_id, 403);

        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'whatsapp'  => 'nullable|string|max:20',
        ]);

        // Mettre à jour les données communes dans users
        $administrateur->update([
            'name'      => $data['name'],
            'prenom'    => $data['prenom'],
            'telephone' => $data['telephone'] ?? $administrateur->telephone,
        ]);

        // Mettre à jour les données spécifiques dans admin_agences
        $cibleAdminAgence?->update([
            'whatsapp' => $data['whatsapp'] ?? $cibleAdminAgence->whatsapp,
        ]);

        return redirect()->route('admin.administrateurs.index')->with('success', 'Administrateur mis à jour.');
    }

    public function destroy(User $administrateur)
    {
        $adminAgence      = auth()->user()->adminAgence;
        $cibleAdminAgence = $administrateur->adminAgence;

        abort_if(!$adminAgence?->est_principal, 403);
        abort_if($cibleAdminAgence?->est_principal, 403, 'Impossible de supprimer l\'admin principal.');
        abort_if($cibleAdminAgence?->agence_id !== $adminAgence?->agence_id, 403);

        // Supprimer d'abord la ligne dans admin_agences, puis dans users
        $cibleAdminAgence?->delete();
        $administrateur->delete();

        return back()->with('success', 'Administrateur supprimé.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect.']);
        }

        $user->update(['password' => Hash::make($data['password'])]);
        return back()->with('success', 'Mot de passe mis à jour.');
    }
}
