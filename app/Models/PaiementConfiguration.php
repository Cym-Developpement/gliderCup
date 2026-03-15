<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaiementConfiguration extends Model
{
    protected $fillable = [
        'adresse_cheque',
        'iban_virement',
        'bic_virement',
    ];

    /**
     * Récupère la configuration unique (singleton)
     */
    public static function getConfiguration()
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
