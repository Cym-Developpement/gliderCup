<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReponseContact extends Notification
{
    use Queueable;

    protected $nom;
    protected $messageOriginal;
    protected $reponse;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $nom, string $messageOriginal, string $reponse)
    {
        $this->nom = $nom;
        $this->messageOriginal = $messageOriginal;
        $this->reponse = $reponse;
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
                    ->subject('Réponse à votre message de contact - ' . $competitionName)
                    ->greeting('Bonjour ' . $this->nom . ',')
                    ->line('Vous avez reçu une réponse concernant votre message de contact.')
                    ->line('**Votre message original :**')
                    ->line($this->messageOriginal)
                    ->line('**Réponse :**')
                    ->line($this->reponse)
                    ->line('Cordialement,')
                    ->salutation($competitionName);
    }
}
