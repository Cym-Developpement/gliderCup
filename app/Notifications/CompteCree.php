<?php

namespace App\Notifications;

use App\Models\Pilote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompteCree extends Notification
{
    use Queueable;

    protected $pilote;

    /**
     * Create a new notification instance.
     */
    public function __construct(Pilote $pilote)
    {
        $this->pilote = $pilote;
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
        $competition = $this->pilote->competition ?? \App\Models\Competition::active();
        $competitionName = $competition ? $competition->nom : 'Wassmer Cup';
        
        $mail = (new MailMessage)
                    ->from(config('mail.from.address'), $competitionName)
                    ->subject('Votre compte a été créé - ' . $competitionName)
                    ->greeting('Bonjour ' . $this->pilote->prenom . ' ' . $this->pilote->nom . ',')
                    ->line('Votre compte a été créé avec succès sur la plateforme ' . $competitionName . '.')
                    ->line('**Vous pouvez maintenant :**')
                    ->line('• Vous connecter à votre espace participant')
                    ->line('• Compléter votre profil et ajouter les documents manquants')
                    ->line('• Consulter le statut de votre inscription')
                    ->line('**Pour vous connecter :**')
                    ->line('1. Allez sur la page de connexion')
                    ->line('2. Utilisez votre adresse e-mail : ' . $this->pilote->email)
                    ->line('3. Si vous n\'avez pas encore défini de mot de passe, utilisez le lien "Mot de passe oublié" pour en créer un.')
                    ->action('Se connecter', url('/login'))
                    ->line('**Important :** N\'oubliez pas de compléter votre profil en ajoutant tous les documents nécessaires depuis votre espace participant.')
                    ->line('Merci de votre participation et à bientôt !');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'pilote_id' => $this->pilote->id,
            'message' => 'Compte créé',
        ];
    }
}
