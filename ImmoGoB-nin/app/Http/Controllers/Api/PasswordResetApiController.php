<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\MobileResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class PasswordResetApiController extends Controller
{
    // ── Étape 1 : Envoyer le lien deep link de réinitialisation ───────────
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // On retourne un succès même si l'email n'existe pas (sécurité)
            return response()->json([
                'message' => 'Si un compte existe avec cet e-mail, vous recevrez un lien de réinitialisation.',
            ]);
        }

        // Générer le token via le broker Laravel (stocké dans password_reset_tokens)
        $token = Password::broker()->createToken($user);

        // Envoyer la notification avec le deep link immogo://
        $user->notify(new MobileResetPasswordNotification($token, $user->email));

        return response()->json([
            'message' => 'Un lien de réinitialisation a été envoyé à votre adresse e-mail.',
        ]);
    }

    // ── Étape 2 : Réinitialiser le mot de passe avec le token ─────────────
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
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
            return response()->json([
                'message' => 'Mot de passe réinitialisé avec succès.',
            ]);
        }

        return response()->json([
            'message' => $status === Password::INVALID_TOKEN
                ? 'Ce lien est invalide ou a expiré. Veuillez en demander un nouveau.'
                : 'Une erreur est survenue. Veuillez réessayer.',
        ], 422);
    }
}
