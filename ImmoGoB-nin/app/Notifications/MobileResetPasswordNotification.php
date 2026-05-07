<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MobileResetPasswordNotification extends Notification
{
    public string $token;
    public string $email;

    public function __construct(string $token, string $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        // Lien web intermédiaire : s'ouvre dans n'importe quel navigateur,
        // tente automatiquement le deep link immogo://, et affiche un fallback web si l'app n'est pas installée.
        $redirectUrl = url('/mobile/reset-password')
            . '?token=' . urlencode($this->token)
            . '&email=' . urlencode($this->email);

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe ImmoGo')
            ->greeting('Bonjour,')
            ->line('Vous avez demandé la réinitialisation de votre mot de passe ImmoGo.')
            ->line('Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe.')
            ->action('Réinitialiser mon mot de passe', $redirectUrl)
            ->line('Ce lien expire dans 60 minutes.')
            ->line('Si vous n\'avez pas fait cette demande, ignorez cet e-mail.')
            ->salutation('L\'équipe ImmoGo');
    }
}
