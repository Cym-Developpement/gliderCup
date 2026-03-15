<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageGroupe extends Model
{
    protected $table = 'messages_groupes';

    protected $fillable = [
        'user_id',
        'message',
        'piece_jointe',
        'sujet',
        'nombre_destinataires',
    ];

    /**
     * Relation avec l'utilisateur (admin)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
