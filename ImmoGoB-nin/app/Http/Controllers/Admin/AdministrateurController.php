<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdministrateurController extends Controller
{
    public function index()
    {
        $agence = auth()->user()->agence;
        $admins = User::where('agence_id', $agence->id)->where('role', 'admin_agence')->get();
        return view('admin.administrateurs.index', compact('admins', 'agence'));
    }

    public function create()
    {
        return view('admin.administrateurs.create');
    }

    public function store(Request $request)
    {
        // Seul l'admin principal peut créer d'autres admins
        abort_if(!auth()->user()->est_principal, 403, 'Seul l\'administrateur principal peut créer des admins.');

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'telephone' => 'nullable|string|max:20',
            'password' => 'required|min:8|confirmed',
        ]);

        $admin = User::create([
            'name' => $data['name'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'telephone' => $data['telephone'] ?? null,
            'role' => 'admin_agence',
            'agence_id' => auth()->user()->agence_id,
            'est_principal' => false,
            'password' => Hash::make($data['password']),
        ]);

        $admin->assignRole('admin_agence');

        return redirect()->route('admin.administrateurs.index')->with('success', 'Administrateur créé.');
    }

    public function edit(User $administrateur)
    {
        abort_if($administrateur->agence_id !== auth()->user()->agence_id, 403);
        return view('admin.administrateurs.edit', compact('administrateur'));
    }

    public function update(Request $request, User $administrateur)
    {
        abort_if($administrateur->agence_id !== auth()->user()->agence_id, 403);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'telephone' => 'nullable|string|max:20',
        ]);

        $administrateur->update($data);
        return redirect()->route('admin.administrateurs.index')->with('success', 'Administrateur mis à jour.');
    }

    public function destroy(User $administrateur)
    {
        abort_if(!auth()->user()->est_principal, 403);
        abort_if($administrateur->est_principal, 403, 'Impossible de supprimer l\'admin principal.');
        abort_if($administrateur->agence_id !== auth()->user()->agence_id, 403);
        $administrateur->delete();
        return back()->with('success', 'Administrateur supprimé.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect.']);
        }

        $user->update(['password' => Hash::make($data['password'])]);
        return back()->with('success', 'Mot de passe mis à jour.');
    }
}
