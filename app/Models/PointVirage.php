<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointVirage extends Model
{
    protected $table = 'points_virage';

    protected $fillable = [
        'competition_id',
        'nom',
        'description',
        'latitude',
        'longitude',
        'points',
        'public',
        'image',
        'tag_id',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'points' => 'integer',
            'public' => 'boolean',
        ];
    }

    /**
     * Relation avec la compétition
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(PointVirageTag::class, 'tag_id');
    }
}
