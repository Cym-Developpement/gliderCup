<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class OpenAipService
{
    /**
     * Récupère la clé API OpenAIP depuis la configuration
     * 
     * @return string|null La clé API ou null si non configurée
     */
    public static function getApiKey(): ?string
    {
        $apiKey = config('services.openaip.api_key');
        
        // Si la clé n'est pas dans le cache de config, essayer directement depuis env
        if (empty($apiKey) && !app()->configurationIsCached()) {
            $apiKey = env('OPENAIP_API_KEY');
        }
        
        // Nettoyer et valider la clé
        if (!empty($apiKey) && is_string($apiKey)) {
            $apiKey = trim($apiKey);
            if (strlen($apiKey) > 0) {
                return $apiKey;
            }
        }
        
        Log::warning('OpenAIP API Key not found', [
            'config_value' => config('services.openaip.api_key'),
            'env_value' => env('OPENAIP_API_KEY'),
            'config_cached' => app()->configurationIsCached()
        ]);
        
        return null;
    }
    
    /**
     * Vérifie si la clé API est configurée
     * 
     * @return bool
     */
    public static function hasApiKey(): bool
    {
        return self::getApiKey() !== null;
    }
    
    /**
     * Récupère l'URL de base de l'API OpenAIP
     * 
     * @return string
     */
    public static function getApiBaseUrl(): string
    {
        return 'https://api.core.openaip.net/api';
    }
    
    /**
     * Construit l'URL complète pour un endpoint de l'API
     * 
     * @param string $endpoint Le endpoint (ex: 'airports', 'airspaces')
     * @return string
     */
    public static function getApiUrl(string $endpoint): string
    {
        return self::getApiBaseUrl() . '/' . ltrim($endpoint, '/');
    }
    
    /**
     * Récupère les headers pour les requêtes API OpenAIP
     * 
     * @return array
     */
    public static function getApiHeaders(): array
    {
        $apiKey = self::getApiKey();
        
        $headers = [
            'Accept' => 'application/json',
        ];
        
        if ($apiKey) {
            $headers['x-openaip-api-key'] = $apiKey;
        }
        
        return $headers;
    }

    /**
     * Recherche un aéroport par code ICAO
     * 
     * @param string $icaoCode Le code ICAO de l'aéroport (ex: LFTH)
     * @return array|null Les données de l'aéroport ou null si non trouvé
     */
    public static function searchAirportByIcao(string $icaoCode): ?array
    {
        $apiKey = self::getApiKey();
        
        if (!$apiKey) {
            Log::error('OpenAIP API Key not configured for airport search');
            return null;
        }

        $url = self::getApiUrl('airports');
        $headers = self::getApiHeaders();
        
        // Nettoyer le code ICAO (majuscules, sans espaces)
        $icaoCode = strtoupper(trim($icaoCode));
        
        // Paramètres selon la documentation OpenAIP
        // L'API utilise le paramètre "search" pour rechercher par code ICAO
        // Note: L'API attend des booléens comme chaînes "true"/"false" et non des booléens PHP
        $params = [
            'page' => 1,
            'limit' => 100,
            'sortDesc' => 'true',  // Chaîne "true" au lieu de booléen true
            'searchOptLwc' => 'true',  // Chaîne "true" au lieu de booléen true
            'search' => $icaoCode,
        ];
        
        Log::info('Paramètres de recherche préparés', [
            'params' => $params,
        ]);

        try {
            // Masquer la clé API dans les logs
            $headersForLog = $headers;
            if (isset($headersForLog['x-openaip-api-key'])) {
                $headersForLog['x-openaip-api-key'] = substr($headersForLog['x-openaip-api-key'], 0, 10) . '...';
            }
            
            Log::info('=== DÉBUT RECHERCHE AÉROPORT OPENAIP ===', [
                'icao' => $icaoCode,
                'url' => $url,
                'params' => $params,
                'headers' => $headersForLog,
            ]);
            
            $response = \Illuminate\Support\Facades\Http::withHeaders($headers)
                ->timeout(10)
                ->get($url, $params);
            
            $responseBody = $response->body();
            Log::info('Réponse API OpenAIP reçue', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body_length' => strlen($responseBody),
                'body_preview' => substr($responseBody, 0, 500), // Premiers 500 caractères
            ]);
            
            // Logger la réponse complète si elle est petite (< 10KB)
            if (strlen($responseBody) < 10000) {
                Log::info('Réponse complète API OpenAIP', [
                    'body' => $responseBody,
                ]);
            }
            
            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Données JSON parsées', [
                    'data_type' => gettype($data),
                    'is_array' => is_array($data),
                    'keys' => is_array($data) ? array_keys($data) : 'N/A',
                    'has_items' => isset($data['items']),
                    'items_count' => isset($data['items']) && is_array($data['items']) ? count($data['items']) : 0,
                    'data_preview' => is_array($data) ? json_encode(array_slice($data, 0, 3, true), JSON_PRETTY_PRINT) : 'N/A',
                ]);
                
                // L'API retourne généralement un tableau d'aéroports
                // On cherche celui qui correspond exactement au code ICAO
                if (isset($data['items']) && is_array($data['items'])) {
                    Log::info('Parcours des items trouvés', [
                        'total_items' => count($data['items']),
                    ]);
                    
                    foreach ($data['items'] as $index => $airport) {
                        $airportIcao = isset($airport['icaoCode']) ? strtoupper(trim($airport['icaoCode'])) : null;
                        
                        Log::info("Item #{$index}", [
                            'airport_icao' => $airportIcao,
                            'airport_name' => $airport['name'] ?? 'N/A',
                            'airport_keys' => is_array($airport) ? array_keys($airport) : 'N/A',
                            'matches' => $airportIcao === $icaoCode,
                        ]);
                        
                        if ($airportIcao === $icaoCode) {
                            Log::info('✅ AÉROPORT TROUVÉ !', [
                                'icao' => $icaoCode,
                                'name' => $airport['name'] ?? 'N/A',
                                'full_data' => json_encode($airport, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                            ]);
                            return $airport;
                        }
                    }
                    
                    Log::warning('Aucun aéroport ne correspond au code ICAO recherché', [
                        'icao_recherche' => $icaoCode,
                        'items_parcourus' => count($data['items']),
                        'codes_icao_trouves' => array_map(function($item) {
                            return isset($item['icaoCode']) ? $item['icaoCode'] : 'N/A';
                        }, $data['items']),
                    ]);
                } else {
                    // Si pas de structure items, vérifier si c'est directement un aéroport
                    if (is_array($data) && !empty($data)) {
                        Log::info('Structure de données différente - pas de clé "items"', [
                            'data_structure' => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                        ]);
                        
                        // Vérifier si les données correspondent directement à un aéroport
                        if (isset($data['icaoCode']) && strtoupper($data['icaoCode']) === $icaoCode) {
                            Log::info('✅ AÉROPORT TROUVÉ (structure directe) !', [
                                'icao' => $icaoCode,
                                'name' => $data['name'] ?? 'N/A',
                            ]);
                            return $data;
                        }
                    }
                }
                
                Log::warning('❌ AÉROPORT NON TROUVÉ', [
                    'icao' => $icaoCode,
                    'data_structure' => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ]);
                return null;
            } else {
                Log::error('❌ ERREUR API OpenAIP', [
                    'icao' => $icaoCode,
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'headers' => $response->headers(),
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('❌ EXCEPTION lors de la recherche d\'aéroport OpenAIP', [
                'icao' => $icaoCode,
                'exception_type' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        } finally {
            Log::info('=== FIN RECHERCHE AÉROPORT OPENAIP ===', [
                'icao' => $icaoCode,
            ]);
        }
    }
}
