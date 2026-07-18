<?php

namespace App\Services\GpsImport;

/**
 * Import de points de virage depuis un CSV en degrés décimaux.
 *
 * Colonnes reconnues par en-tête (séparateur « , » ou « ; ») :
 *   nom (name/libelle), latitude (lat), longitude (lng/lon), description (desc).
 * Sans en-tête, l'ordre positionnel attendu est : nom, latitude, longitude, description.
 */
class CsvImport implements GpsImportInterface
{
    private const ALIASES = [
        'nom' => ['nom', 'name', 'libelle', 'libellé', 'label', 'titre'],
        'latitude' => ['latitude', 'lat', 'y'],
        'longitude' => ['longitude', 'lng', 'lon', 'long', 'x'],
        'description' => ['description', 'desc', 'commentaire', 'comment', 'note'],
    ];

    public static function parse(string $content): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $content);

        // Détecter le séparateur sur la première ligne non vide
        $delimiter = ',';
        foreach ($lines as $l) {
            if (trim($l) === '') {
                continue;
            }
            $delimiter = substr_count($l, ';') > substr_count($l, ',') ? ';' : ',';
            break;
        }

        $rows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $rows[] = str_getcsv($line, $delimiter, '"', '\\');
        }
        if (empty($rows)) {
            return [];
        }

        // En-tête si latitude + longitude reconnues, sinon ordre positionnel
        $map = self::detectHeader($rows[0]);
        $startIdx = 0;
        if ($map !== null) {
            $startIdx = 1;
        } else {
            $map = ['nom' => 0, 'latitude' => 1, 'longitude' => 2, 'description' => 3];
        }

        $points = [];
        for ($i = $startIdx; $i < count($rows); $i++) {
            $cols = $rows[$i];
            $nom = isset($map['nom']) ? trim($cols[$map['nom']] ?? '') : '';
            $lat = self::parseDecimal($cols[$map['latitude']] ?? '', $delimiter);
            $lon = self::parseDecimal($cols[$map['longitude']] ?? '', $delimiter);

            if ($nom === '' || $lat === null || $lon === null) {
                continue;
            }
            if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
                continue;
            }

            $desc = isset($map['description']) ? trim($cols[$map['description']] ?? '') : '';

            $points[] = [
                'nom' => $nom,
                'description' => $desc !== '' ? $desc : null,
                'latitude' => round($lat, 8),
                'longitude' => round($lon, 8),
            ];
        }

        return $points;
    }

    private static function detectHeader(array $cols): ?array
    {
        $lower = array_map(fn($c) => strtolower(trim($c)), $cols);
        $map = [];
        foreach (self::ALIASES as $key => $aliases) {
            foreach ($aliases as $alias) {
                $idx = array_search($alias, $lower, true);
                if ($idx !== false) {
                    $map[$key] = $idx;
                    break;
                }
            }
        }

        return (isset($map['latitude']) && isset($map['longitude'])) ? $map : null;
    }

    /**
     * Parse un décimal en tolérant la virgule française quand le séparateur de colonnes est « ; ».
     */
    private static function parseDecimal($raw, string $delimiter): ?float
    {
        $s = trim((string) $raw);
        if ($s === '') {
            return null;
        }
        if ($delimiter === ';') {
            $s = str_replace(',', '.', $s);
        }

        return is_numeric($s) ? (float) $s : null;
    }
}
