<?php

namespace App\Notifications;

use App\Models\Pilote;
use App\Models\Planeur;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class InscriptionValidee extends Notification
{
    use Queueable;

    protected $pilote;
    protected $planeurs;

    /**
     * Create a new notification instance.
     */
    public function __construct(Pilote $pilote, $planeurs = null)
    {
        $this->pilote = $pilote;
        // Accepter soit une collection, soit un planeur unique, soit null
        if ($planeurs instanceof Collection) {
            $this->planeurs = $planeurs;
        } elseif ($planeurs instanceof Planeur) {
            $this->planeurs = collect([$planeurs]);
        } else {
            $this->planeurs = collect();
        }
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
                    ->subject('Inscription validée - Compétition de Planeur ' . $competitionName)
                    ->greeting('Bonjour ' . $this->pilote->prenom . ' ' . $this->pilote->nom . ',')
                    ->line('Nous avons le plaisir de vous informer que votre inscription à la compétition de planeur ' . $competitionName . ' a été **validée** !')
                    ->line('Vous êtes maintenant officiellement inscrit à l\'événement.')
                    ->line('**Informations du pilote :**')
                    ->line('Nom : ' . $this->pilote->nom)
                    ->line('Prénom : ' . $this->pilote->prenom)
                    ->line('Qualité : ' . $this->pilote->qualite)
                    ->line('Date de naissance : ' . $this->pilote->date_naissance->format('d/m/Y'))
                    ->line('E-mail : ' . $this->pilote->email);

        if ($this->pilote->telephone) {
            $mail->line('Téléphone : ' . $this->pilote->telephone);
        }

        if ($this->pilote->numero_ffvp) {
            $mail->line('N° FFVP : ' . $this->pilote->numero_ffvp);
        }

        if ($this->pilote->club) {
            $mail->line('Club : ' . $this->pilote->club);
        }

        // Afficher les informations des planeurs
        if ($this->planeurs->count() > 0) {
            if ($this->planeurs->count() === 1) {
                $planeur = $this->planeurs->first();
                $mail->line('')
                     ->line('**Informations du planeur :**');

                if ($planeur->marque) {
                    $mail->line('Marque : ' . $planeur->marque);
                }

                $mail->line('Modèle : ' . $planeur->modele);

                if ($planeur->type) {
                    $mail->line('Type : ' . ucfirst($planeur->type));
                }

                $mail->line('Immatriculation : ' . $planeur->immatriculation);
            } else {
                $mail->line('')
                     ->line('**Planeurs inscrits (' . $this->planeurs->count() . ') :**');
                
                foreach ($this->planeurs as $index => $planeur) {
                    $mail->line('**Planeur ' . ($index + 1) . ' :**');
                    
                    if ($planeur->marque) {
                        $mail->line('Marque : ' . $planeur->marque);
                    }
                    
                    $mail->line('Modèle : ' . $planeur->modele);
                    
                    if ($planeur->type) {
                        $mail->line('Type : ' . ucfirst($planeur->type));
                    }
                    
                    $mail->line('Immatriculation : ' . $planeur->immatriculation);
                    
                    if ($index < $this->planeurs->count() - 1) {
                        $mail->line('');
                    }
                }
            }
        }

        $mail->line('')
             ->line('**Important :** Pour accéder à votre espace participant et consulter les détails de votre inscription, vous pouvez vous connecter sur le site en utilisant votre adresse e-mail et le mot de passe que vous avez défini (ou utilisez le lien "Mot de passe oublié" si vous ne l\'avez pas encore défini).')
             ->action('Accéder à mon espace', url('/login'))
             ->line('Nous vous contacterons prochainement avec plus d\'informations sur l\'événement.')
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
            'planeur_ids' => $this->planeurs->pluck('id')->toArray(),
            'statut' => 'validee',
        ];
    }
}

