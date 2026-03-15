<?php

namespace App\Notifications;

use App\Models\Planeur;
use App\Models\Pilote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlaneurValide extends Notification
{
    use Queueable;

    protected $planeur;
    protected $piloteInscrit;

    /**
     * Create a new notification instance.
     */
    public function __construct(Planeur $planeur, Pilote $piloteInscrit)
    {
        $this->planeur = $planeur;
        $this->piloteInscrit = $piloteInscrit;
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
        $competition = $this->planeur->competition ?? \App\Models\Competition::active();
        $competitionName = $competition ? $competition->nom : 'Wassmer Cup';
        
        $mail = (new MailMessage)
                    ->from(config('mail.from.address'), $competitionName)
                    ->subject('Validation de votre planeur - Compétition de Planeur ' . $competitionName)
                    ->greeting('Bonjour ' . $notifiable->prenom . ' ' . $notifiable->nom . ',')
                    ->line('Nous avons le plaisir de vous informer que votre planeur a été **validé** pour la compétition de planeur ' . $competitionName . '.')
                    ->line('**Informations du planeur validé :**');

        if ($this->planeur->marque) {
            $mail->line('Marque : ' . $this->planeur->marque);
        }

        $mail->line('Modèle : ' . $this->planeur->modele);

        if ($this->planeur->type) {
            $mail->line('Type : ' . ucfirst($this->planeur->type));
        }

        $mail->line('Immatriculation : ' . $this->planeur->immatriculation)
             ->line('**Pilote inscrit avec ce planeur :**')
             ->line('Nom : ' . $this->piloteInscrit->nom)
             ->line('Prénom : ' . $this->piloteInscrit->prenom)
             ->line('Email : ' . $this->piloteInscrit->email);

        if ($this->piloteInscrit->telephone) {
            $mail->line('Téléphone : ' . $this->piloteInscrit->telephone);
        }

        $mail->line('Votre planeur est maintenant officiellement inscrit à l\'événement.')
             ->line('Nous vous contacterons prochainement avec plus d\'informations sur l\'événement.')
             ->line('Merci de votre participation !');

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
            'planeur_id' => $this->planeur->id,
            'pilote_inscrit_id' => $this->piloteInscrit->id,
            'statut' => 'validee',
        ];
    }
}

