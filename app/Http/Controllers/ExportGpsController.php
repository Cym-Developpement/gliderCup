<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\PointVirage;
use App\Models\PointVirageTag;
use App\Services\GpsExport\GpsExportInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
     * Le paramètre optionnel ?tag={id} limite l'export à un libellé,
     * et ?tag=aucun aux points sans libellé.
     *
     * GET /export/gps/{format}
     */
    public function export(Request $request, string $format)
    {
        if (!isset(static::$formats[$format])) {
            abort(404, "Format d'export inconnu : $format");
        }

        $competition = Competition::active();
        if (!$competition) {
            abort(404, 'Aucune compétition active.');
        }

        $query = PointVirage::where('competition_id', $competition->id);
        $nomExport = $competition->nom;

        $tagParam = $request->query('tag');
        if ($tagParam === 'aucun') {
            $query->whereNull('tag_id');
            $nomExport .= ' sans libellé';
        } elseif ($tagParam !== null) {
            $tag = PointVirageTag::where('competition_id', $competition->id)->findOrFail($tagParam);
            $query->where('tag_id', $tag->id);
            $nomExport .= ' ' . $tag->nom;
        }

        $points = $query->get();

        /** @var GpsExportInterface $exportClass */
        $exportClass = static::$formats[$format];

        return $exportClass::export($points, $nomExport);
    }

    public function downloadCarte()
    {
        $competition = Competition::active();
        if (!$competition) {
            abort(404, 'Aucune compétition active.');
        }

        $slug = Str::slug($competition->nom);
        $relativePath = "maps/{$slug}.png";

        if (!Storage::disk('public')->exists($relativePath)) {
            abort(404, 'Carte non générée. Demandez à un administrateur de la régénérer.');
        }

        $fullPath = Storage::disk('public')->path($relativePath);

        return response()->download($fullPath, "carte_{$slug}.png", [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'ETag' => md5_file($fullPath),
        ]);
    }
}
