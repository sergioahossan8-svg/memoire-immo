<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class PasswordResetController extends Controller
{
    // ── Étape 1 : Afficher le formulaire "Mot de passe oublié" ────────────────
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    // ── Étape 2 : Envoyer le lien de réinitialisation par email ──────────────
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email'    => 'Veuillez saisir une adresse e-mail valide.',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'Un lien de réinitialisation a été envoyé à votre adresse e-mail.');
        }

        return back()->withErrors([
            'email' => $status === Password::INVALID_USER
                ? 'Aucun compte n\'est associé à cette adresse e-mail.'
                : 'Impossible d\'envoyer le lien. Veuillez réessayer dans quelques instants.',
        ]);
    }

    // ── Étape 3 : Afficher le formulaire de nouveau mot de passe ─────────────
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    // ── Page universelle : tente le deep link mobile, fallback sur le web ─────
    public function mobileRedirect(Request $request)
    {
        $token = $request->query('token', '');
        $email = $request->query('email', '');

        return view('auth.mobile-reset-redirect', [
            'token' => $token,
            'email' => $email,
        ]);
    }
    // ── Étape 4 : Enregistrer le nouveau mot de passe ────────────────────────
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'min:8', 'confirmed'],
        ], [
            'email.required'            => 'L\'adresse e-mail est obligatoire.',
            'email.email'               => 'Adresse e-mail invalide.',
            'password.required'         => 'Le mot de passe est obligatoire.',
            'password.min'              => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed'        => 'Les mots de passe ne correspondent pas.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
        }

        return back()->withErrors([
            'email' => $status === Password::INVALID_TOKEN
                ? 'Ce lien de réinitialisation est invalide ou a expiré. Veuillez en demander un nouveau.'
                : 'Une erreur est survenue. Veuillez réessayer.',
        ]);
    }
}
