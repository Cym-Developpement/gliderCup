<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competition extends Model
{
    /**
     * ID de la compétition active (solution temporaire, évoluera à l'avenir)
     */
    private static ?int $activeCompetitionId = null;

    protected $fillable = [
        'nom',
        'description',
        'reglement',
        'date_debut',
        'date_fin',
        'lieu',
        'code_aeroport',
        'limite_planeurs',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
            'actif' => 'boolean',
        ];
    }

    /**
     * Relation avec les pilotes
     */
    public function pilotes(): HasMany
    {
        return $this->hasMany(Pilote::class);
    }

    /**
     * Relation avec les planeurs
     */
    public function planeurs(): HasMany
    {
        return $this->hasMany(Planeur::class);
    }

    /**
     * Relation avec les points de virage
     */
    public function pointsVirage(): HasMany
    {
        return $this->hasMany(PointVirage::class);
    }

    /**
     * Obtenir la compétition active
     * Utilise une variable privée statique pour stocker l'ID (solution temporaire)
     */
    public static function active()
    {
        // Si l'ID n'est pas défini, utiliser l'ID 2 par défaut (ou chercher dans la base)
        if (self::$activeCompetitionId === null) {
            self::$activeCompetitionId = 2; // ID par défaut, à ajuster selon vos besoins
        }

        return static::find(self::$activeCompetitionId);
    }

    /**
     * Définir l'ID de la compétition active
     */
    public static function setActiveId(int $id): void
    {
        self::$activeCompetitionId = $id;
    }

    /**
     * Obtenir l'ID de la compétition active
     */
    public static function getActiveId(): ?int
    {
        if (self::$activeCompetitionId === null) {
            self::$activeCompetitionId = 2; // ID par défaut
        }
        return self::$activeCompetitionId;
    }
}
