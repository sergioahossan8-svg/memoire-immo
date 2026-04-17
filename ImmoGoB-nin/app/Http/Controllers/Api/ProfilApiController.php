<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfilApiController extends Controller
{
    public function index()
    {
        $user   = auth()->user();
        $client = $user->client; // données spécifiques dans la table clients

        return response()->json([
            'user' => [
                'id'        => $user->id,
                'name'      => $user->name,
                'prenom'    => $user->prenom,
                'email'     => $user->email,
                'telephone' => $user->telephone,
                'ville'     => $client?->ville,
                'adresse'   => $client?->adresse,
                'avatar'    => $client?->avatar ? \Storage::url($client->avatar) : null,
            ]
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'ville'     => 'nullable|string|max:100',
            'adresse'   => 'nullable|string|max:200',
            'avatar'    => 'nullable|image|max:2048',
            'password'  => 'nullable|min:8|confirmed',
        ]);

        // Mettre à jour les données communes dans la table users
        $userUpdate = [
            'name'      => $data['name'],
            'prenom'    => $data['prenom'],
            'telephone' => $data['telephone'] ?? $user->telephone,
        ];

        if (!empty($data['password'])) {
            $userUpdate['password'] = Hash::make($data['password']);
        }

        $user->update($userUpdate);

        // Mettre à jour les données spécifiques dans la table clients
        $clientData = [];
        if (isset($data['ville']))   $clientData['ville']   = $data['ville'];
        if (isset($data['adresse'])) $clientData['adresse'] = $data['adresse'];

        if ($request->hasFile('avatar')) {
            $clientData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if (!empty($clientData)) {
            $user->client()->updateOrCreate(['user_id' => $user->id], $clientData);
        }

        $client = $user->fresh()->client;

        return response()->json([
            'message' => 'Profil mis à jour.',
            'user' => [
                'id'        => $user->id,
                'name'      => $user->name,
                'prenom'    => $user->prenom,
                'email'     => $user->email,
                'telephone' => $user->telephone,
                'ville'     => $client?->ville,
                'adresse'   => $client?->adresse,
                'avatar'    => $client?->avatar ? \Storage::url($client->avatar) : null,
            ]
        ]);
    }
}
