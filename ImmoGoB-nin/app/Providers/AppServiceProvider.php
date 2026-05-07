<?php

namespace App\Providers;

use Illuminate\Database\Schema\Builder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Builder::defaultStringLength(191);

        // URL du lien de réinitialisation pour le web uniquement
        // (le mobile utilise MobileResetPasswordNotification avec un deep link)
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $email = urlencode($notifiable->getEmailForPasswordReset());
            return url("/reinitialiser-mot-de-passe/{$token}?email={$email}");
        });
    }
}
