<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AdminAgence;
use App\Models\Agence;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AgenceController extends Controller
{
    public function index()
    {
        $agences = Agence::with('adminPrincipal')->withCount('biens')->latest()->paginate(20);
        $stats = [
            'agences_actives'  => Agence::where('statut', 'actif')->count(),
            'administrateurs'  => AdminAgence::count(),
            'annonces_publiees' => \App\Models\Bien::where('is_published', true)->count(),
        ];
        return view('superadmin.agences.index', compact('agences', 'stats'));
    }

    public function create()
    {
        return view('superadmin.agences.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom_commercial'  => 'required|string|max:200',
            'secteur'         => 'required|in:Résidentiel,Commercial,Industriel,Mixte',
            'ville'           => 'required|string',
            'adresse_complete' => 'required|string',
            'email'           => 'required|email|unique:agences',
            'telephone'       => 'nullable|string|max:20',
            'logo'            => 'nullable|image|max:2048',
            // Admin principal
            'admin_nom'       => 'required|string|max:100',
            'admin_prenom'    => 'required|string|max:100',
            'admin_email'     => 'required|email|unique:users,email',
            'admin_telephone' => 'nullable|string|max:20',
            'admin_whatsapp'  => 'nullable|string|max:20',
            'admin_fonction'  => 'nullable|string|max:100',
            'admin_password'  => 'required|min:8',
        ]);

        // 1. Créer l'agence
        $agence = Agence::create([
            'nom_commercial'  => $data['nom_commercial'],
            'secteur'         => $data['secteur'],
            'ville'           => $data['ville'],
            'adresse_complete' => $data['adresse_complete'],
            'email'           => $data['email'],
            'telephone'       => $data['telephone'] ?? null,
            'logo'            => $request->hasFile('logo') ? $request->file('logo')->store('agences', 'public') : null,
            'statut'          => 'actif',
        ]);

        // 2. Créer l'admin principal dans la table mère users (données communes)
        $admin = User::create([
            'name'      => $data['admin_nom'],
            'prenom'    => $data['admin_prenom'],
            'email'     => $data['admin_email'],
            'telephone' => $data['admin_telephone'] ?? null,
            'role'      => 'admin_agence',
            'password'  => Hash::make($data['admin_password']),
        ]);

        // 3. Créer dans la table spécialisée admin_agences (données spécifiques)
        AdminAgence::create([
            'user_id'       => $admin->id,
            'agence_id'     => $agence->id,
            'est_principal' => true,
            'whatsapp'      => $data['admin_whatsapp'] ?? null,
        ]);

        $admin->assignRole('admin_agence');

        \App\Models\ActivityLog::log('agence_created', 'Agence créée : ' . $agence->nom_commercial, $agence);

        return redirect()->route('superadmin.agences.index')
            ->with('success', 'Agence "' . $agence->nom_commercial . '" créée avec succès.');
    }

    public function show(Agence $agence)
    {
        $agence->load(['adminAgences.user', 'biens.photos']);
        return view('superadmin.agences.show', compact('agence'));
    }

    public function edit(Agence $agence)
    {
        return view('superadmin.agences.edit', compact('agence'));
    }

    public function update(Request $request, Agence $agence)
    {
        $data = $request->validate([
            'nom_commercial'  => 'required|string|max:200',
            'secteur'         => 'required|in:Résidentiel,Commercial,Industriel,Mixte',
            'ville'           => 'required|string',
            'adresse_complete' => 'required|string',
            'telephone'       => 'nullable|string|max:20',
        ]);

        $agence->update($data);
        return redirect()->route('superadmin.agences.index')->with('success', 'Agence mise à jour.');
    }

    public function destroy(Agence $agence)
    {
        $agence->delete();
        return back()->with('success', 'Agence supprimée.');
    }

    public function updateStatut(Request $request, Agence $agence)
    {
        $request->validate(['statut' => 'required|in:actif,en_attente,suspendu']);
        $agence->update(['statut' => $request->statut]);
        return back()->with('success', 'Statut de l\'agence mis à jour.');
    }
}
