<?php

namespace App\Notifications;

use App\Models\Competition;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class MessageEnvoye extends Notification
{
    use Queueable;

    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
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
        $competition = $this->message->pilote->competition ?? Competition::active();
        $competitionName = $competition ? $competition->nom : 'Wassmer Cup';
        
        $adminName = $this->message->user ? $this->message->user->name : 'Administration';

        $mail = (new MailMessage)
                    ->from(config('mail.from.address'), $competitionName)
                    ->subject('Nouveau message des organisateurs - ' . $competitionName)
                    ->greeting('Bonjour ' . $this->message->pilote->prenom . ' ' . $this->message->pilote->nom . ',')
                    ->line('Vous avez reçu un nouveau message des organisateurs de la compétition ' . $competitionName . '.')
                    ->line('**Message de ' . $adminName . ' :**')
                    ->line($this->message->message)
                    ->action('Voir mes messages', route('dashboard'))
                    ->line('Vous pouvez répondre à ce message en vous connectant à votre espace personnel.');

        // Ajouter la pièce jointe si elle existe
        if ($this->message->piece_jointe && Storage::disk('public')->exists($this->message->piece_jointe)) {
            $mail->attach(Storage::disk('public')->path($this->message->piece_jointe), [
                'as' => basename($this->message->piece_jointe),
            ]);
        }

        return $mail;
    }
}
