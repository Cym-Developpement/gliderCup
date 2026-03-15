<?php

namespace App\Notifications;

use App\Models\Pilote;
use App\Models\Planeur;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NouvelleInscriptionAdmin extends Notification
{
    use Queueable;

    protected $pilote;
    protected $planeur;

    /**
     * Create a new notification instance.
     */
    public function __construct(Pilote $pilote, ?Planeur $planeur = null)
    {
        $this->pilote = $pilote;
        $this->planeur = $planeur;
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
                    ->subject('Nouvelle inscription - ' . $competitionName . ' (Copie)')
                    ->greeting('Bonjour,')
                    ->line('Une nouvelle inscription a été enregistrée sur le site ' . $competitionName . '.')
                    ->line('**Récapitulatif de l\'inscription :**')
                    ->line('')
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

        if ($this->pilote->adresse || $this->pilote->code_postal || $this->pilote->ville) {
            $adresseComplete = trim(($this->pilote->adresse ?? '') . ' ' . ($this->pilote->code_postal ?? '') . ' ' . ($this->pilote->ville ?? ''));
            if ($adresseComplete) {
                $mail->line('Adresse : ' . $adresseComplete);
            }
        }

        $mail->line('Statut : ' . ucfirst(str_replace('_', ' ', $this->pilote->statut)));

        // Documents fournis
        $documentsFournis = [];
        if ($this->pilote->autorisation_parentale) {
            $documentsFournis[] = 'Autorisation parentale';
        }
        if ($this->pilote->feuille_declarative_qualifications) {
            $documentsFournis[] = 'Feuille déclarative qualifications';
        }
        if ($this->pilote->visite_medicale_classe_2) {
            $documentsFournis[] = 'Visite médicale classe 2';
        }
        if ($this->pilote->spl_valide) {
            $documentsFournis[] = 'SPL Valide';
        }

        if (count($documentsFournis) > 0) {
            $mail->line('**Documents fournis :**');
            foreach ($documentsFournis as $doc) {
                $mail->line('• ' . $doc);
            }
        }

        if ($this->planeur) {
            $mail->line('')
                 ->line('**Informations du planeur inscrit :**');

            if ($this->planeur->marque) {
                $mail->line('Marque : ' . $this->planeur->marque);
            }

            $mail->line('Modèle : ' . $this->planeur->modele);

            if ($this->planeur->type) {
                $mail->line('Type : ' . ucfirst($this->planeur->type));
            }

            $mail->line('Immatriculation : ' . $this->planeur->immatriculation);

            // Documents du planeur fournis
            $documentsPlaneurFournis = [];
            if ($this->planeur->cdn_cen) {
                $documentsPlaneurFournis[] = 'CDN / CEN';
            }
            if ($this->planeur->responsabilite_civile) {
                $documentsPlaneurFournis[] = 'Responsabilité civile';
            }

            if (count($documentsPlaneurFournis) > 0) {
                $mail->line('**Documents du planeur fournis :**');
                foreach ($documentsPlaneurFournis as $doc) {
                    $mail->line('• ' . $doc);
                }
            }
        } else {
            $mail->line('')
                 ->line('**Aucun planeur n\'a été inscrit pour cette participation.**');
        }

        $mail->line('')
             ->action('Voir les inscriptions', url('/admin/dashboard'))
             ->line('Merci de valider ou refuser cette inscription depuis le tableau de bord administrateur.');

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
            'planeur_id' => $this->planeur?->id,
            'statut' => $this->pilote->statut,
        ];
    }
}

