<?php

namespace App\Http\Controllers;

use App\Services\OpenAipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class OpenAIPTileController extends Controller
{
    /**
     * Proxy pour servir les tuiles OpenAIP avec cache
     * 
     * @param int $z Niveau de zoom
     * @param int $x Coordonnée X
     * @param int $y Coordonnée Y
     * @return Response
     */
    public function getTile(int $z, int $x, int $y)
    {
        // Logger toutes les requêtes entrantes
        Log::info('=== REQUÊTE TUILE OPENAIP REÇUE ===', [
            'z' => $z,
            'x' => $x,
            'y' => $y,
            'request_method' => request()->method(),
            'request_url' => request()->fullUrl(),
            'request_path' => request()->path(),
        ]);
        
        $apiKey = OpenAipService::getApiKey();
        
        Log::info('Clé API récupérée', [
            'has_key' => !empty($apiKey),
            'key_preview' => $apiKey ? substr($apiKey, 0, 10) . '...' : 'N/A'
        ]);
        
        if (!$apiKey) {
            Log::error('Clé API OpenAIP non configurée');
            return response('Clé API OpenAIP non configurée', 500)
                ->header('Content-Type', 'text/plain');
        }

        // Clé de cache pour cette tuile
        $cacheKey = "openaip_tile_{$z}_{$x}_{$y}";
        $storagePath = "openaip/tiles/{$z}/{$x}/{$y}.png";
        
        Log::debug('Vérification cache tuile', [
            'storage_path' => $storagePath,
            'exists' => Storage::exists($storagePath)
        ]);

        // Vérifier si la tuile est en cache (7 jours)
        // Vérifier d'abord le fichier, puis le cache Laravel
        if (Storage::exists($storagePath)) {
            $tilePath = Storage::path($storagePath);
            $fileTime = filemtime($tilePath);
            
            // Vérifier si le fichier a moins de 7 jours
            if ($fileTime && (time() - $fileTime) < (7 * 24 * 60 * 60)) {
                // Mettre à jour le cache Laravel
                Cache::put($cacheKey, true, now()->addDays(7));
                
                return response()->file($tilePath, [
                    'Content-Type' => 'image/png',
                    'Cache-Control' => 'public, max-age=604800', // 7 jours en secondes
                ]);
            }
        }

        // Télécharger la tuile depuis OpenAIP
        try {
            // Utiliser le header d'authentification au lieu du paramètre URL
            $tileUrl = "https://api.tiles.openaip.net/api/data/openaip/{$z}/{$x}/{$y}.png";
            $headers = OpenAipService::getApiHeaders();
            
            Log::info('Téléchargement tuile OpenAIP', [
                'z' => $z,
                'x' => $x,
                'y' => $y,
                'url' => $tileUrl,
                'has_auth_header' => isset($headers['x-openaip-api-key'])
            ]);

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->withOptions(['verify' => true])
                ->get($tileUrl);

            if (!$response->successful()) {
                $httpStatus = $response->status();
                
                Log::warning('Erreur HTTP lors du téléchargement de la tuile OpenAIP', [
                    'status' => $httpStatus,
                    'z' => $z,
                    'x' => $x,
                    'y' => $y,
                    'url' => str_replace($apiKey, substr($apiKey, 0, 10) . '...', $tileUrl),
                    'body_preview' => substr($response->body(), 0, 200)
                ]);
                
                // Renvoyer le même code HTTP que l'API OpenAIP (403, 404, etc.)
                // Retourner une image transparente 1x1 avec le même statut HTTP
                // Ne pas mettre en cache pour permettre de réessayer
                return response(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='), $httpStatus)
                    ->header('Content-Type', 'image/png')
                    ->header('Cache-Control', 'no-cache, no-store, must-revalidate'); // Pas de cache pour les erreurs
            }

            $tileData = $response->body();
            $tileSize = strlen($tileData);

            // Ne pas sauvegarder les tuiles de taille 0 (erreur ou réponse vide)
            if ($tileSize === 0) {
                Log::warning('Tuile OpenAIP de taille 0 - non sauvegardée', [
                    'z' => $z,
                    'x' => $x,
                    'y' => $y,
                    'status' => $response->status()
                ]);
                
                return response(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='), 200)
                    ->header('Content-Type', 'image/png')
                    ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
            }

            // Vérifier que c'est bien une image PNG
            if (substr($tileData, 0, 8) !== "\x89PNG\r\n\x1a\n") {
                Log::warning('Réponse OpenAIP n\'est pas une image PNG valide', [
                    'z' => $z,
                    'x' => $x,
                    'y' => $y,
                    'size' => $tileSize,
                    'preview' => substr($tileData, 0, 50)
                ]);
                
                return response(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='), 200)
                    ->header('Content-Type', 'image/png')
                    ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
            }

            // Si la tuile est très petite (< 500 octets), c'est probablement une image transparente/vide
            // C'est normal pour certaines zones sans données OpenAIP, mais on la sauvegarde quand même
            if ($tileSize < 500) {
                Log::debug('Tuile OpenAIP très petite (probablement transparente/vide)', [
                    'z' => $z,
                    'x' => $x,
                    'y' => $y,
                    'size' => $tileSize
                ]);
            }

            // Créer le répertoire si nécessaire
            $directory = dirname($storagePath);
            if (!Storage::exists($directory)) {
                $created = Storage::makeDirectory($directory);
                Log::info('Répertoire créé pour tuile OpenAIP', [
                    'directory' => $directory,
                    'created' => $created
                ]);
            }
            
            // Stocker la tuile dans le storage (seulement si taille > 0)
            $saved = Storage::put($storagePath, $tileData);
            Log::info('Tentative de sauvegarde tuile', [
                'path' => $storagePath,
                'saved' => $saved,
                'size' => strlen($tileData),
                'full_path' => Storage::path($storagePath)
            ]);
            
            if (!$saved) {
                Log::error('Échec de la sauvegarde de la tuile', [
                    'path' => $storagePath,
                    'full_path' => Storage::path($storagePath)
                ]);
            }

            // Mettre en cache pendant 7 jours (604800 secondes)
            Cache::put($cacheKey, true, now()->addDays(7));

            Log::info('Tuile OpenAIP téléchargée et mise en cache', [
                'z' => $z,
                'x' => $x,
                'y' => $y,
                'size' => strlen($tileData),
                'storage_path' => $storagePath
            ]);

            // Retourner la tuile
            return response($tileData, 200)
                ->header('Content-Type', 'image/png')
                ->header('Cache-Control', 'public, max-age=604800'); // 7 jours

        } catch (\Exception $e) {
            Log::error('Exception lors du téléchargement de la tuile OpenAIP', [
                'message' => $e->getMessage(),
                'z' => $z,
                'x' => $x,
                'y' => $y
            ]);

            // Retourner une image transparente en cas d'erreur
            return response(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='), 200)
                ->header('Content-Type', 'image/png')
                ->header('Cache-Control', 'public, max-age=3600');
        }
    }
}
