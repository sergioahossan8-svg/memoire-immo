<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('client.profil', compact('user'));
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

        // Mettre à jour la session de localisation si la ville a changé
        if (!empty($data['ville'])) {
            session(['ville_active' => $data['ville']]);
        }

        return back()->with('success', 'Profil mis à jour.');
    }

    public function notifications()
    {
        $notifications = auth()->user()->notificationsImmogo()->latest()->paginate(20);
        auth()->user()->notificationsImmogo()->where('lu', false)->update(['lu' => true]);
        return view('client.notifications', compact('notifications'));
    }
}
