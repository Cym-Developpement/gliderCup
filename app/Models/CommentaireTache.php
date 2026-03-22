<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentaireTache extends Model
{
    protected $table = 'commentaires_taches';

    protected $fillable = ['tache_id', 'contenu'];

    public function tache(): BelongsTo
    {
        return $this->belongsTo(Tache::class);
    }
}
