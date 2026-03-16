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
            (new Circle($center, '22c55e1a', 3, '22c55e99', true))
                ->setRadius(1500)
        );

        $points = PointVirage::where('competition_id', $competition->id)->get();

        $tempMarkers = [];
        foreach ($points as $index => $point) {
            $pos = new LatLng($point->latitude, $point->longitude);
            $markerPath = self::generateMarkerPng($index + 1);
            $tempMarkers[] = $markerPath;
            $ptMarkers = new Markers($markerPath);
            $ptMarkers->setAnchor(Markers::ANCHOR_CENTER, Markers::ANCHOR_BOTTOM);
            $ptMarkers->addMarker($pos);
            $map->draw()->addMarkers($ptMarkers);
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

        $legendText .= "\n>>Du " . $competition->date_debut->format('d/m/Y') . " au " . $competition->date_fin->format('d/m/Y') . "\n";
        $legendText .= "\n>>wassmercup.fr\n";

        $logoSrc = imagecreatefrompng(public_path('img/logo.png'));
        $logoW = (int) (imagesx($logoSrc) * 3);
        $logoH = (int) (imagesy($logoSrc) * 3);
        $logoBig = imagecreatetruecolor($logoW, $logoH);
        imagealphablending($logoBig, false);
        imagesavealpha($logoBig, true);
        imagefill($logoBig, 0, 0, imagecolorallocatealpha($logoBig, 0, 0, 0, 127));
        imagecopyresampled($logoBig, $logoSrc, 0, 0, 0, 0, $logoW, $logoH, imagesx($logoSrc), imagesy($logoSrc));
        $tmpLogo = sys_get_temp_dir() . '/logo_2x.png';
        imagepng($logoBig, $tmpLogo);
        imagedestroy($logoSrc);
        imagedestroy($logoBig);
        $tempMarkers[] = $tmpLogo;

        $titre = $competition->nom;
        $map->draw()->addDraw(
            new Legend(Legend::ALIGN_RIGHT, $legendText, 25, '000000', 'ffffff', 32, $tmpLogo, $titre)
        );

        $slug = Str::slug($competition->nom);
        $relativePath = "maps/{$slug}.png";

        Storage::disk('public')->makeDirectory('maps');

        $fullPath = Storage::disk('public')->path($relativePath);
        $map->saveImage($fullPath);

        foreach ($tempMarkers as $tmp) {
            @unlink($tmp);
        }

        return $relativePath;
    }

    private static function generateMarkerPng(int $numero): string
    {
        $basePath = public_path('img/marker-base.png');
        $tmpPng = sys_get_temp_dir() . '/marker_' . $numero . '.png';

        $img = imagecreatefrompng($basePath);
        imagealphablending($img, true);
        imagesavealpha($img, true);

        $w = imagesx($img);
        $h = imagesy($img);

        $black = imagecolorallocate($img, 0, 0, 0);
        $text = (string) $numero;
        $fontPath = __DIR__ . '/../../vendor/ycdev/php-osm-static-aero/src/resources/SpaceMono-Bold.ttf';
        $fontSize = 28;
        $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
        $tw = abs($bbox[2] - $bbox[0]);
        $th = abs($bbox[7] - $bbox[1]);
        $cx = ($w / 2) - ($tw / 2);
        $cy = ($h * 0.22) + ($th / 2);
        imagettftext($img, $fontSize, 0, (int) $cx, (int) $cy, $black, $fontPath, $text);

        imagealphablending($img, false);
        imagepng($img, $tmpPng);
        imagedestroy($img);

        return $tmpPng;
    }
}
