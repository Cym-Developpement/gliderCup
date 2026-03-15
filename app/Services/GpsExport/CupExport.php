<?php

namespace App\Services\GpsExport;

use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CupExport implements GpsExportInterface
{
    public static function export(Collection $points, string $competitionName): StreamedResponse
    {
        $filename = \Illuminate\Support\Str::slug($competitionName) . '.cup';

        return response()->streamDownload(function () use ($points) {
            $header = 'name,code,country,lat,lon,elev,style,rwydir,rwylen,freq,desc';
            echo $header . "\r\n";

            foreach ($points as $point) {
                $fields = [
                    '"' . str_replace('"', '""', $point->nom) . '"',
                    '""',
                    'FR',
                    static::formatLatitude($point->latitude),
                    static::formatLongitude($point->longitude),
                    '0.0m',
                    '1', // 1 = Point de repère
                    '',
                    '',
                    '',
                    '"' . str_replace('"', '""', $point->description ?? '') . '"',
                ];

                echo implode(',', $fields) . "\r\n";
            }
        }, $filename, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Convertit une latitude décimale en format CUP : DDMM.MMMN/S
     * Exemple : 48.12345678 → 4807.407N
     */
    private static function formatLatitude(float $decimal): string
    {
        $hemisphere = $decimal >= 0 ? 'N' : 'S';
        $decimal = abs($decimal);
        $degrees = (int) $decimal;
        $minutes = ($decimal - $degrees) * 60;

        return sprintf('%02d%06.3f%s', $degrees, $minutes, $hemisphere);
    }

    /**
     * Convertit une longitude décimale en format CUP : DDDMM.MMME/W
     * Exemple : 7.12345678 → 00707.407E
     */
    private static function formatLongitude(float $decimal): string
    {
        $hemisphere = $decimal >= 0 ? 'E' : 'W';
        $decimal = abs($decimal);
        $degrees = (int) $decimal;
        $minutes = ($decimal - $degrees) * 60;

        return sprintf('%03d%06.3f%s', $degrees, $minutes, $hemisphere);
    }
}
