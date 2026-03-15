<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'pilote_id',
        'user_id',
        'message',
        'piece_jointe',
        'lu',
    ];

    protected function casts(): array
    {
        return [
            'lu' => 'boolean',
        ];
    }

    /**
     * Relation avec le pilote
     */
    public function pilote(): BelongsTo
    {
        return $this->belongsTo(Pilote::class);
    }

    /**
     * Relation avec l'utilisateur (admin)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
