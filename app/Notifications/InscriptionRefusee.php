<?php

namespace App\Notifications;

use App\Models\Pilote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InscriptionRefusee extends Notification
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
        
        return (new MailMessage)
                    ->from(config('mail.from.address'), $competitionName)
                    ->subject('Inscription refusée - Compétition de Planeur ' . $competitionName)
                    ->greeting('Bonjour ' . $this->pilote->prenom . ' ' . $this->pilote->nom . ',')
                    ->line('Nous vous informons que votre inscription à la compétition de planeur ' . $competitionName . ' a été **refusée**.')
                    ->line('Nous vous remercions de votre intérêt pour cet événement.')
                    ->line('Si vous avez des questions concernant cette décision, n\'hésitez pas à nous contacter.')
                    ->line('Cordialement,')
                    ->line('L\'équipe ' . $competitionName);
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
            'statut' => 'refusee',
        ];
    }
}

