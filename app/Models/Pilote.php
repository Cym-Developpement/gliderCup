<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use App\Notifications\ResetPasswordNotification;

class Pilote extends Authenticatable
{
    use Notifiable, CanResetPassword;

    protected $fillable = [
        'competition_id',
        'nom',
        'prenom',
        'qualite',
        'date_naissance',
        'email',
        'password',
        'identifiant_virement',
        'helloasso_checkout_intent_id',
        'telephone',
        'licence',
        'club',
        'adresse',
        'code_postal',
        'ville',
        'numero_ffvp',
        'autorisation_parentale',
        'feuille_declarative_qualifications',
        'visite_medicale_classe_2',
        'spl_valide',
        'statut',
        'paiement_valide',
        'montant_custom',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
            'password' => 'hashed',
            'paiement_valide' => 'boolean',
        ];
    }

    /**
     * Relation avec la compétition
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * Relation avec les planeurs (propriétaire)
     */
    public function planeursProprietaire(): HasMany
    {
        return $this->hasMany(Planeur::class);
    }

    /**
     * Relation many-to-many avec les planeurs (inscriptions)
     */
    public function planeurs(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Planeur::class, 'pilote_planeur');
    }

    /**
     * Relation avec les messages
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
