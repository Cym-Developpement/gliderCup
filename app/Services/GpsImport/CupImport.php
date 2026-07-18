<?php

namespace App\Services\GpsImport;

/**
 * Import de points de virage depuis un fichier SeeYou (.cup).
 * Inverse de {@see \App\Services\GpsExport\CupExport}.
 */
class CupImport implements GpsImportInterface
{
    public static function parse(string $content): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $points = [];
        $map = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            // Fin de la section waypoints : début des tâches
            if (str_starts_with($line, '-----') || stripos($line, 'Related Tasks') !== false) {
                break;
            }

            $cols = str_getcsv($line, ',', '"', '\\');

            // Première ligne significative : détecter l'en-tête (colonnes lat + lon)
            if ($map === null) {
                $lower = array_map(fn($c) => strtolower(trim($c)), $cols);
                if (in_array('lat', $lower, true) && in_array('lon', $lower, true)) {
                    $map = [
                        'nom' => array_search('name', $lower, true),
                        'description' => array_search('desc', $lower, true),
                        'latitude' => array_search('lat', $lower, true),
                        'longitude' => array_search('lon', $lower, true),
                    ];
                    continue;
                }
                // Pas d'en-tête reconnu : positions CUP standard
                $map = ['nom' => 0, 'description' => 10, 'latitude' => 3, 'longitude' => 4];
            }

            $nom = trim($cols[$map['nom']] ?? '');
            $latRaw = trim($cols[$map['latitude']] ?? '');
            $lonRaw = trim($cols[$map['longitude']] ?? '');
            if ($nom === '' || $latRaw === '' || $lonRaw === '') {
                continue;
            }

            $lat = self::parseCoord($latRaw);
            $lon = self::parseCoord($lonRaw);
            if ($lat === null || $lon === null) {
                continue;
            }

            $desc = ($map['description'] !== false && $map['description'] !== null)
                ? trim($cols[$map['description']] ?? '')
                : '';

            $points[] = [
                'nom' => $nom,
                'description' => $desc !== '' ? $desc : null,
                'latitude' => $lat,
                'longitude' => $lon,
            ];
        }

        return $points;
    }

    /**
     * Convertit une coordonnée CUP (DDMM.mmmN/S ou DDDMM.mmmE/W) en degrés décimaux.
     * Ex. « 4807.407N » → 48.12345, « 00015.000W » → -0.25
     */
    private static function parseCoord(string $raw): ?float
    {
        if (!preg_match('/^(\d+\.?\d*)\s*([NSEW])$/i', $raw, $m)) {
            return null;
        }
        $num = $m[1];
        $hemi = strtoupper($m[2]);

        // Les minutes occupent toujours 2 chiffres avant le point décimal ;
        // tout ce qui précède constitue les degrés (2 pour lat, 3 pour lon).
        $dot = strpos($num, '.');
        $minStart = ($dot !== false ? $dot : strlen($num)) - 2;
        if ($minStart < 1) {
            return null;
        }

        $degrees = (float) substr($num, 0, $minStart);
        $minutes = (float) substr($num, $minStart);
        if ($minutes >= 60) {
            return null;
        }

        $decimal = $degrees + $minutes / 60;
        if ($hemi === 'S' || $hemi === 'W') {
            $decimal = -$decimal;
        }

        return round($decimal, 8);
    }
}
