<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    protected $fillable = [
        'nom',
        'email',
        'message',
        'repondu',
        'user_id',
        'reponse',
        'reponse_envoyee_at',
    ];

    protected function casts(): array
    {
        return [
            'repondu' => 'boolean',
            'reponse_envoyee_at' => 'datetime',
        ];
    }

    /**
     * Relation avec l'utilisateur (admin) qui a répondu
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
