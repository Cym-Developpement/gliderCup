<?php

namespace App\Http\Controllers;

use App\Services\OpenAipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIPController extends Controller
{
    /**
     * Proxy pour récupérer les données OpenAIP depuis le serveur
     * Cela évite d'exposer la clé API côté client
     */
    public function getData(Request $request)
    {
        // Utiliser uniquement la classe de service pour récupérer la clé
        $apiKey = OpenAipService::getApiKey();
        
        if (!$apiKey) {
            return response()->json(['error' => 'Clé API OpenAIP non configurée'], 500);
        }

        $type = $request->query('type', 'airports'); // airports, airspaces, navaids
        $country = $request->query('country', 'FR');
        $bbox = $request->query('bbox'); // Format: minLon,minLat,maxLon,maxLat

        // Utiliser la classe de service pour construire l'URL et les headers
        $url = OpenAipService::getApiUrl($type);
        $headers = OpenAipService::getApiHeaders();
        
        $params = [
            'country' => $country,
        ];

        if ($bbox) {
            $params['bbox'] = $bbox;
        }

        try {
            // Masquer la clé API dans les logs
            $headersForLog = $headers;
            if (isset($headersForLog['x-openaip-api-key'])) {
                $headersForLog['x-openaip-api-key'] = substr($headersForLog['x-openaip-api-key'], 0, 10) . '...';
            }
            
            Log::info('Appel API OpenAIP', [
                'url' => $url,
                'params' => $params,
                'headers' => $headersForLog
            ]);
            
            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->get($url, $params);
            
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                Log::error('Erreur API OpenAIP', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'headers' => $response->headers()
                ]);
                return response()->json([
                    'error' => 'Erreur lors de la récupération des données OpenAIP',
                    'status' => $response->status(),
                    'message' => $response->body()
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'appel API OpenAIP', [
                'url' => $url,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Erreur de connexion à l\'API OpenAIP',
                'message' => $e->getMessage(),
                'url' => $url
            ], 500);
        }
    }
}
