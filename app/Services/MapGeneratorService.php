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

        $baseMarkerPath = public_path('img/marker-base.png');
        $baseImg = imagecreatefrompng($baseMarkerPath);
        $origW = imagesx($baseImg);
        $origH = imagesy($baseImg);
        $newW = (int) ($origW / 1.5);
        $newH = (int) ($origH / 1.5);
        $resized = imagecreatetruecolor($newW, $newH);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefill($resized, 0, 0, $transparent);
        imagecopyresampled($resized, $baseImg, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        $tmpBase = sys_get_temp_dir() . '/marker_base_small.png';
        imagepng($resized, $tmpBase);
        imagedestroy($baseImg);
        imagedestroy($resized);

        $markers = new Markers($tmpBase);
        $markers->setAnchor(Markers::ANCHOR_CENTER, Markers::ANCHOR_BOTTOM);
        $markers->addMarker($center);
        $map->draw()->addMarkers($markers);

        $points = PointVirage::where('competition_id', $competition->id)->get();

        $tempMarkers = [$tmpBase];
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
        $logoW = imagesx($logoSrc) * 2;
        $logoH = imagesy($logoSrc) * 2;
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
        $svg = file_get_contents(public_path('img/path1.svg'));
        $svg = str_replace('#PP', (string) $numero, $svg);

        $tmpSvg = sys_get_temp_dir() . '/marker_' . $numero . '.svg';
        $tmpPng = sys_get_temp_dir() . '/marker_' . $numero . '.png';
        file_put_contents($tmpSvg, $svg);

        if (shell_exec('which rsvg-convert 2>/dev/null')) {
            shell_exec("rsvg-convert -w 105 -h 90 {$tmpSvg} -o {$tmpPng}");
        } elseif (extension_loaded('imagick')) {
            $im = new \Imagick();
            $im->setResolution(72, 72);
            $im->readImage($tmpSvg);
            $im->setImageFormat('png');
            $im->resizeImage(105, 90, \Imagick::FILTER_LANCZOS, 1);
            $im->writeImage($tmpPng);
            $im->destroy();
        } else {
            // Fallback GD : cercle blanc avec numéro sur fond transparent
            $w = 105;
            $h = 90;
            $img = imagecreatetruecolor($w, $h);
            imagealphablending($img, false);
            imagesavealpha($img, true);
            $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
            imagefill($img, 0, 0, $transparent);
            imagealphablending($img, true);
            $white = imagecolorallocate($img, 255, 255, 255);
            $black = imagecolorallocate($img, 0, 0, 0);
            $blue = imagecolorallocate($img, 37, 99, 235);
            imagefilledellipse($img, $w / 2, $h / 2 - 5, 50, 50, $white);
            imageellipse($img, $w / 2, $h / 2 - 5, 50, 50, $blue);
            $font = 5;
            $text = (string) $numero;
            $tw = strlen($text) * imagefontwidth($font);
            $th = imagefontheight($font);
            imagestring($img, $font, ($w - $tw) / 2, ($h - $th) / 2 - 5, $text, $black);
            imagealphablending($img, false);
            imagepng($img, $tmpPng);
            imagedestroy($img);
        }

        @unlink($tmpSvg);

        return $tmpPng;
    }
}
