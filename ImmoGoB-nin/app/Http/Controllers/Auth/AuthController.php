<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            \App\Models\ActivityLog::log('login', 'Connexion réussie');
            return $this->redirectByRole(Auth::user());
        }

        return back()->withErrors(['email' => 'Identifiants incorrects.'])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
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
            'terms'     => 'accepted',
        ]);

        // Créer l'utilisateur dans la table mère (données communes uniquement)
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
        Auth::login($user);

        return redirect()->route('home')->with('success', 'Bienvenue sur ImmoGo !');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        \App\Models\ActivityLog::log('logout', 'Déconnexion');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    private function redirectByRole(User $user)
    {
        return match ($user->role) {
            'super_admin' => redirect()->route('superadmin.dashboard'),
            'admin_agence' => redirect()->route('admin.dashboard'),
            default => redirect()->route('home'),
        };
    }
}
