<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        foreach ($roles as $role) {
            if ($user->role === $role) {
                return $next($request);
            }
        }

        // Message personnalisé selon le rôle de l'utilisateur
        if (in_array($user->role, ['admin_agence', 'super_admin'])) {
            return redirect()->back()->with(
                'error_403',
                'En tant qu\'administrateur, vous ne pouvez pas effectuer de réservation ou de paiement. Cette fonctionnalité est réservée aux clients.'
            );
        }

        abort(403, 'Accès non autorisé.');
    }
}
