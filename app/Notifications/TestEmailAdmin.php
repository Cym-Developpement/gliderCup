<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TestEmailAdmin extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $competition = \App\Models\Competition::active();
        $competitionName = $competition ? $competition->nom : 'Wassmer Cup';
        
        return (new MailMessage)
                    ->from(config('mail.from.address'), $competitionName)
                    ->subject('Test d\'envoi d\'email - ' . $competitionName)
                    ->greeting('Bonjour,')
                    ->line('Ceci est un email de test pour vérifier que la configuration email fonctionne correctement.')
                    ->line('Si vous recevez cet email, cela signifie que la configuration est correcte.')
                    ->line('**Informations de test :**')
                    ->line('Date : ' . now()->format('d/m/Y H:i:s'))
                    ->line('Application : ' . $competitionName)
                    ->line('Configuration email : ' . config('mail.default'))
                    ->line('Merci !');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'test' => true,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}

