<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MessageContact extends Notification
{
    use Queueable;

    protected $nom;
    protected $email;
    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $nom, string $email, string $message)
    {
        $this->nom = $nom;
        $this->email = $email;
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
        $competition = \App\Models\Competition::active();
        $competitionName = $competition ? $competition->nom : 'Wassmer Cup';
        
        return (new MailMessage)
                    ->from(config('mail.from.address'), $competitionName)
                    ->subject('Nouveau message de contact - ' . $this->nom)
                    ->replyTo($this->email, $this->nom)
                    ->greeting('Bonjour,')
                    ->line('Vous avez reçu un nouveau message de contact depuis le site ' . $competitionName . '.')
                    ->line('**De :** ' . $this->nom)
                    ->line('**Email :** ' . $this->email)
                    ->line('**Message :**')
                    ->line($this->message)
                    ->line('Vous pouvez répondre directement à cet email pour contacter ' . $this->nom . '.');
    }
}
