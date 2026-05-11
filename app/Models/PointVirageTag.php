<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointVirageTag extends Model
{
    protected $table = 'points_virage_tags';

    protected $fillable = [
        'competition_id',
        'nom',
        'couleur',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function pointsVirage(): HasMany
    {
        return $this->hasMany(PointVirage::class, 'tag_id');
    }
}
