<?php

namespace App\Services\GpsExport;

use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface GpsExportInterface
{
    /**
     * Génère et retourne la réponse de téléchargement pour le format donné.
     */
    public static function export(Collection $points, string $competitionName): StreamedResponse;
}
