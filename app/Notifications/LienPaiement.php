<?php

namespace App\Notifications;

use App\Models\Pilote;
use App\Models\Competition;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LienPaiement extends Notification
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
        $competition = $this->pilote->competition ?? Competition::active();
        $competitionName = $competition ? $competition->nom : 'Wassmer Cup';
        
        // Utiliser l'identifiant_virement au lieu de l'ID
        $identifiant = $this->pilote->identifiant_virement;
        if (!$identifiant) {
            // Fallback sur l'ID si l'identifiant n'existe pas (anciens pilotes)
            $identifiant = $this->pilote->id;
        }
        $lienPaiement = url(route('paiement.public', ['identifiantVirement' => $identifiant]));
        
        $mail = (new MailMessage)
            ->from(config('mail.from.address'), $competitionName)
            ->subject('Lien de paiement - ' . $competitionName)
            ->greeting('Bonjour ' . $this->pilote->prenom . ' ' . $this->pilote->nom . ',')
            ->line('Vous recevez cet e-mail car un administrateur vous a envoyé le lien pour procéder au paiement de votre inscription.')
            ->line('**Pour finaliser votre inscription, veuillez procéder au paiement en cliquant sur le lien ci-dessous :**')
            ->action('Accéder à la page de paiement', $lienPaiement)
            ->line('Ce lien est unique et vous permet d\'accéder directement à votre page de paiement.')
            ->line('Si vous avez des questions, n\'hésitez pas à nous contacter.')
            ->line('Cordialement,')
            ->line('L\'équipe ' . $competitionName);

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
            'message' => 'Lien de paiement envoyé',
        ];
    }
}
