<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\PointVirage;
use App\Services\GpsExport\GpsExportInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExportGpsController extends Controller
{
    /**
     * Formats d'export disponibles : slug => classe
     */
    private static array $formats = [
        'cup' => \App\Services\GpsExport\CupExport::class,
    ];

    /**
     * Enregistre un format d'export.
     */
    public static function registerFormat(string $slug, string $class): void
    {
        if (!is_a($class, GpsExportInterface::class, true)) {
            throw new \InvalidArgumentException("$class doit implémenter GpsExportInterface");
        }
        static::$formats[$slug] = $class;
    }

    /**
     * Retourne la liste des formats disponibles.
     */
    public static function getFormats(): array
    {
        return static::$formats;
    }

    /**
     * Exporte les points de virage dans le format demandé.
     *
     * GET /export/gps/{format}
     */
    public function export(string $format)
    {
        if (!isset(static::$formats[$format])) {
            abort(404, "Format d'export inconnu : $format");
        }

        $competition = Competition::active();
        if (!$competition) {
            abort(404, 'Aucune compétition active.');
        }
        $points = PointVirage::where('competition_id', $competition->id)->get();

        /** @var GpsExportInterface $exportClass */
        $exportClass = static::$formats[$format];

        return $exportClass::export($points, $competition->nom);
    }
}
