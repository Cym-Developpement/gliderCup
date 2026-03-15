<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisiteurUnique extends Model
{
    protected $table = 'visiteurs_uniques';

    protected $fillable = [
        'competition_id',
        'ip_address',
        'user_agent',
        'date_visite',
    ];

    protected function casts(): array
    {
        return [
            'date_visite' => 'date',
        ];
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }
}
