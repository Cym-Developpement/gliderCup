<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Planeur extends Model
{
    protected $fillable = [
        'competition_id',
        'pilote_id',
        'modele',
        'marque',
        'type',
        'immatriculation',
        'statut',
        'cdn_cen',
        'responsabilite_civile',
    ];

    /**
     * Relation avec la compétition
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * Relation avec le pilote propriétaire
     */
    public function piloteProprietaire(): BelongsTo
    {
        return $this->belongsTo(Pilote::class, 'pilote_id');
    }

    /**
     * Relation many-to-many avec les pilotes (inscriptions)
     */
    public function pilotes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Pilote::class, 'pilote_planeur');
    }
}
