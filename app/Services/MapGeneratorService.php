<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\PointVirage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ycdev\OsmStaticAero\LatLng;
use Ycdev\OsmStaticAero\PaperMap;
use Ycdev\OsmStaticAero\Circle;
use Ycdev\OsmStaticAero\Legend;
use Ycdev\OsmStaticAero\Markers;
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
            ['zoom' => 11, 'factor' => 2.0, 'bordure' => 5],
            [TileLayer::OSMFR, TileLayer::OPENAIP]
        );

        $map->draw()->addDraw(
            (new Circle($center, '2563eb1a', 3, '2563eb99', true))
                ->setRadius(1000)
        );

        $markers = new Markers(public_path('img/marker-base.png'));
        $markers->setAnchor(Markers::ANCHOR_CENTER, Markers::ANCHOR_BOTTOM);
        $markers->addMarker($center);
        $map->draw()->addMarkers($markers);

        $points = PointVirage::where('competition_id', $competition->id)->get();

        foreach ($points as $point) {
            $pos = new LatLng($point->latitude, $point->longitude);
            $map->draw()->addDraw(new Text($pos, $point->nom, 30, '000000'));
        }

        $compositionLabels = [
            0 => "Asphalte", 1 => "Béton", 2 => "Herbe", 3 => "Sable",
            4 => "Eau", 5 => "Bitume", 6 => "Brique", 7 => "Macadam",
            8 => "Pierre", 9 => "Corail", 10 => "Argile", 11 => "Latérite",
            12 => "Gravier", 13 => "Terre", 14 => "Glace", 15 => "Neige",
            16 => "Caoutchouc", 17 => "Métal", 18 => "Aluminium",
            19 => "Acier perforé", 20 => "Bois", 21 => "Non bitumineux", 22 => "Inconnu",
        ];

        $legendText = "\n# AERODROME :\n\n";
        $airportName = $airportData['name'] ?? '';
        $icao = $airportData['icaoCode'] ?? $competition->code_aeroport;
        $legendText .= "## {$airportName} ({$icao})\n";

        if (!empty($airportData['frequencies'])) {
            $legendText .= $airportData['frequencies'][0]['value'] . " Mhz\n";
        }

        if (!empty($airportData['runways'])) {
            $allRunways = [];
            $width = 0;
            $used = [];
            foreach ($airportData['runways'] as $i => $runway) {
                if (($runway['operations'] ?? null) !== 0) continue;
                if (in_array($i, $used)) continue;

                $comp = $runway['surface']['composition'] ?? 22;
                $compositionCode = is_array($comp) ? ($comp[0] ?? 22) : $comp;
                $compositionText = $compositionLabels[$compositionCode] ?? 'Inconnu';

                $foundPair = false;
                $d1 = intval($runway['designator']);
                foreach ($airportData['runways'] as $j => $other) {
                    if (($other['operations'] ?? null) !== 0) continue;
                    if ($i !== $j && !in_array($j, $used)) {
                        $d2 = intval($other['designator']);
                        if (abs($d1 - $d2) == 18) {
                            $len1 = $runway['dimension']['length']['value'];
                            $len2 = $other['dimension']['length']['value'];
                            $lengthStr = ($len1 == $len2) ? "{$len1} m" : "{$len1} m/{$len2} m";
                            $allRunways[] = $runway['designator'] . "/" . $other['designator'] . " ({$lengthStr}, {$compositionText})";
                            $used[] = $i;
                            $used[] = $j;
                            $foundPair = true;
                            break;
                        }
                    }
                }
                if (!$foundPair) {
                    $len = $runway['dimension']['length']['value'];
                    $allRunways[] = $runway['designator'] . " ({$len} m, {$compositionText})";
                    $used[] = $i;
                }
                if ($width == 0) {
                    $width = $runway['dimension']['width']['value'] ?? 0;
                }
            }
            if (!empty($allRunways)) {
                $legendText .= implode(' / ', $allRunways) . " largeur : {$width} m\n";
            }
        }

        $legendText .= "\n# POINTS DE VIRAGE :\n\n";
        foreach ($points as $index => $point) {
            $legendText .= '## #' . ($index + 1) . ' ' . $point->nom . "\n";
        }

        $titre = $competition->nom;
        $map->draw()->addDraw(
            new Legend(Legend::ALIGN_RIGHT, $legendText, 25, '000000', 'ffffff', 32, null, $titre)
        );

        $slug = Str::slug($competition->nom);
        $relativePath = "maps/{$slug}.png";

        Storage::disk('public')->makeDirectory('maps');

        $fullPath = Storage::disk('public')->path($relativePath);
        $map->saveImage($fullPath);

        return $relativePath;
    }
}
