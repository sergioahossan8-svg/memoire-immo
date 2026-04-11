<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfilApiController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return response()->json([
            'user' => [
                'id'        => $user->id,
                'name'      => $user->name,
                'prenom'    => $user->prenom,
                'email'     => $user->email,
                'telephone' => $user->telephone,
                'ville'     => $user->ville,
                'adresse'   => $user->adresse,
                'avatar'    => $user->avatar ? \Storage::url($user->avatar) : null,
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

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profil mis à jour.',
            'user' => [
                'id'        => $user->id,
                'name'      => $user->name,
                'prenom'    => $user->prenom,
                'email'     => $user->email,
                'telephone' => $user->telephone,
                'ville'     => $user->ville,
                'adresse'   => $user->adresse,
                'avatar'    => $user->avatar ? \Storage::url($user->avatar) : null,
            ]
        ]);
    }
}
