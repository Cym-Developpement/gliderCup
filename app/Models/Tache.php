<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tache extends Model
{
    protected $table = 'taches';

    protected $fillable = ['competition_id', 'intitule', 'personne', 'statut'];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function commentaires(): HasMany
    {
        return $this->hasMany(CommentaireTache::class);
    }
}
