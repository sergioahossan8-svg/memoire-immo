<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    public function index()
    {
        $user       = auth()->user();
        $superAdmin = $user->superAdmin; // données spécifiques dans super_admins
        return view('superadmin.profil', compact('user', 'superAdmin'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'whatsapp'  => 'nullable|string|max:20',
            'password'  => 'nullable|min:8|confirmed',
        ]);

        // Mettre à jour les données communes dans la table mère users
        $userUpdate = [
            'name'      => $data['name'],
            'prenom'    => $data['prenom'],
            'telephone' => $data['telephone'] ?? $user->telephone,
        ];

        if (!empty($data['password'])) {
            $userUpdate['password'] = Hash::make($data['password']);
        }

        $user->update($userUpdate);

        // Mettre à jour les données spécifiques dans la table super_admins
        $user->superAdmin()->updateOrCreate(
            ['user_id' => $user->id],
            ['whatsapp' => $data['whatsapp'] ?? null]
        );

        return back()->with('success', 'Profil mis à jour.');
    }
}
