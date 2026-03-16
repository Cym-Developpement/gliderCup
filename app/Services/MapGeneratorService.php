<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\PointVirage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ycdev\OsmStaticAero\LatLng;
use Ycdev\OsmStaticAero\PaperMap;
use Ycdev\OsmStaticAero\Circle;
use Ycdev\OsmStaticAero\Text;
use Ycdev\OsmStaticAero\TileLayer;
use Ycdev\PaperSize\PaperSize;

class MapGeneratorService
{
    public static function generate(): string
    {
        $competition = Competition::active();
        if (!$competition) {
            throw new \RuntimeException('Aucune compétition active.');
        }

        $filename = 'airports/' . $competition->code_aeroport . '.json';
        if (!Storage::disk('private')->exists($filename)) {
            throw new \RuntimeException('Données aérodrome introuvables pour ' . $competition->code_aeroport);
        }

        $airportData = json_decode(Storage::disk('private')->get($filename), true);
        $centerLat = $airportData['geometry']['coordinates'][1];
        $centerLng = $airportData['geometry']['coordinates'][0];
        $center = new LatLng($centerLat, $centerLng);

        $map = new PaperMap(
            PaperSize::landscape(PaperSize::A3),
            $center,
            ['zoom' => 12, 'factor' => 2.0, 'bordure' => 5],
            [TileLayer::OSMFR, TileLayer::OPENAIP]
        );

        $map->draw()->addDraw(
            (new Circle($center, '2563eb1a', 3, '2563eb99'))
                ->setRadius(1000)
        );

        $points = PointVirage::where('competition_id', $competition->id)->get();

        foreach ($points as $point) {
            $pos = new LatLng($point->latitude, $point->longitude);
            $map->draw()->addDraw(new Text($pos, $point->nom, 30, '000000'));
        }

        $slug = Str::slug($competition->nom);
        $relativePath = "maps/{$slug}.png";

        Storage::disk('public')->makeDirectory('maps');

        $fullPath = Storage::disk('public')->path($relativePath);
        $map->saveImage($fullPath);

        return $relativePath;
    }
}
