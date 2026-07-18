<?php

namespace App\Services\GpsImport;

interface GpsImportInterface
{
    /**
     * Analyse le contenu brut d'un fichier et retourne la liste des points.
     *
     * Chaque point : ['nom' => string, 'description' => ?string,
     *                 'latitude' => float, 'longitude' => float]
     *
     * @return array<int, array{nom:string, description:?string, latitude:float, longitude:float}>
     */
    public static function parse(string $content): array;
}
