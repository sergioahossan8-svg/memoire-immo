<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Identifiants incorrects.'], 401);
        }

        if ($user->role !== 'client') {
            return response()->json(['message' => 'Accès non autorisé pour ce type de compte.'], 403);
        }

        $token = $user->createToken('immogo-mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'email'     => 'required|email|unique:users',
            'telephone' => 'nullable|string|max:20',
            'ville'     => 'required|string|max:100',
            'adresse'   => 'nullable|string|max:200',
            'password'  => 'required|min:8|confirmed',
        ]);

        // Créer l'utilisateur dans la table mère users (données communes)
        $user = User::create([
            'name'      => $data['name'],
            'prenom'    => $data['prenom'],
            'email'     => $data['email'],
            'telephone' => $data['telephone'] ?? null,
            'role'      => 'client',
            'password'  => Hash::make($data['password']),
        ]);

        // Créer l'entrée dans la table spécialisée clients (données spécifiques)
        Client::create([
            'user_id' => $user->id,
            'ville'   => $data['ville'],
            'adresse' => $data['adresse'] ?? null,
        ]);

        $user->assignRole('client');

        $token = $user->createToken('immogo-mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté avec succès.']);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $this->formatUser($request->user())]);
    }

    private function formatUser(User $user): array
    {
        $client = $user->client; // données spécifiques dans la table clients

        return [
            'id'        => $user->id,
            'name'      => $user->name,
            'prenom'    => $user->prenom,
            'email'     => $user->email,
            'telephone' => $user->telephone,
            'ville'     => $client?->ville,
            'adresse'   => $client?->adresse,
            'role'      => $user->role,
            'avatar'    => $client?->avatar ? storage_url($client->avatar) : null,
        ];
    }
}
