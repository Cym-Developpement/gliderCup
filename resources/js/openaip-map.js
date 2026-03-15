import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Corriger les icônes par défaut de Leaflet (problème connu avec les bundlers)
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
});

/**
 * Initialise une carte OpenAIP avec Leaflet
 * @param {string} mapId - ID du conteneur HTML pour la carte
 * @param {Object} options - Options de configuration
 * @param {Array<number>} options.center - [latitude, longitude] pour centrer la carte
 * @param {number} options.zoom - Niveau de zoom initial
 * @param {string} options.tileLayer - URL du tile layer (par défaut OpenStreetMap)
 */
export function initOpenAIPMap(mapId, options = {}) {
    const defaultOptions = {
        center: [46.6, 2.5], // Centre de la France par défaut
        zoom: 6,
        // Utiliser les tuiles OpenAIP comme fond de carte par-dessus OSM
        useOpenAIPTiles: true, // Utiliser les tuiles OpenAIP (nécessite une clé API)
        tileLayer: 'https://api.tiles.openaip.net/api/data/openaip/{z}/{x}/{y}.png',
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | <a href="https://www.openaip.net">openAIP Data (CC-BY-NC)</a>',
        autoLoadOpenAIP: false, // Ne pas charger automatiquement les données OpenAIP (tuiles uniquement)
        openAIPCountry: 'FR' // Pays par défaut pour les données OpenAIP
    };

    const config = { ...defaultOptions, ...options };

    // Initialiser la carte
    const map = L.map(mapId).setView(config.center, config.zoom);

    // Ajouter OSM classique comme fond de carte de base
    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
        tileSize: 256,
    });
    osmLayer.addTo(map);

    // Ajouter les tuiles OpenAIP par-dessus OSM si activées
    if (config.useOpenAIPTiles) {
        // Créer une couche personnalisée pour OpenAIP
        // Format OpenAIP : https://api.tiles.openaip.net/api/data/openaip/z/x/y.png
        // Utilisation du proxy Laravel pour protéger la clé API
        const openaipLayer = L.tileLayer('', {
            attribution: config.attribution,
            minZoom: 4,
            maxZoom: 14,
            tileSize: 256,
            tms: false, // OpenAIP n'utilise pas TMS, format standard XYZ
            detectRetina: true,
            opacity: 0.7, // Transparence pour voir OSM en dessous
        });
        
        // Utiliser le proxy Laravel pour les tuiles (cache la clé API et les tuiles)
        // Format : /api/openaip/tiles/{z}/{x}/{y}.png
        // Le proxy télécharge, cache et sert les tuiles avec la clé API côté serveur
        openaipLayer.getTileUrl = function(coords) {
            const z = coords.z;
            const x = coords.x;
            // Format standard XYZ (pas TMS), donc on utilise coords.y directement
            const y = coords.y;
            // Utiliser le proxy Laravel au lieu de l'URL directe OpenAIP
            const url = `/api/openaip/tiles/${z}/${x}/${y}.png`;
            console.log('OpenAIP tile URL:', url, 'coords:', {z, x, y});
            return url;
        };
        
        // Ajouter un listener pour voir quand les tuiles sont chargées
        openaipLayer.on('tileload', function(e) {
            console.log('Tuile OpenAIP chargée:', e.tile.src);
        });
        
        // Gérer les erreurs de chargement des tuiles
        openaipLayer.on('tileerror', function(error, tile) {
            console.warn('Erreur de chargement de la tuile OpenAIP:', {
                url: tile.src,
                error: error
            });
        });
        
        // Logger les tuiles qui ne se chargent pas
        openaipLayer.on('tileloadstart', function(e) {
            console.debug('Début chargement tuile OpenAIP:', e.tile.src);
        });
        
        openaipLayer.addTo(map);
        
        // Stocker les références aux couches
        map._baseLayers = {
            osm: osmLayer,
            openaip: openaipLayer
        };
    } else {
        // Si pas de tuiles OpenAIP, juste OSM
        map._baseLayers = {
            osm: osmLayer
        };
    }

    // Stocker les couches OpenAIP pour pouvoir les supprimer lors du rechargement
    map._openaipLayers = {
        airports: null,
        airspaces: null
    };

    // Charger automatiquement les données OpenAIP si activé
    if (config.autoLoadOpenAIP) {
        // Attendre que la carte soit prête avant de charger les données
        setTimeout(() => {
            loadOpenAIPDataForMap(map, config.openAIPCountry);
        }, 500);
        
        // Recharger les données quand la carte est déplacée ou zoomée (avec debounce)
        let reloadTimeout;
        map.on('moveend', () => {
            clearTimeout(reloadTimeout);
            reloadTimeout = setTimeout(() => {
                loadOpenAIPDataForMap(map, config.openAIPCountry);
            }, 500);
        });
    }

    return map;
}

/**
 * Charge les données OpenAIP pour la zone visible de la carte
 * @param {L.Map} map - Instance de la carte Leaflet
 * @param {string} country - Code pays (ex: 'FR')
 */
async function loadOpenAIPDataForMap(map, country = 'FR') {
    const bounds = map.getBounds();
    const bbox = `${bounds.getWest()},${bounds.getSouth()},${bounds.getEast()},${bounds.getNorth()}`;
    
    // Charger les aéroports
    try {
        const airportsUrl = `/api/openaip/data?type=airports&country=${country}&bbox=${bbox}`;
        
        // Supprimer l'ancienne couche si elle existe
        if (map._openaipLayers && map._openaipLayers.airports) {
            map.removeLayer(map._openaipLayers.airports);
            map._openaipLayers.airports = null;
        }
        
        const layer = await loadOpenAIPData(map, airportsUrl, {
            color: '#FF2D20',
            weight: 2,
            opacity: 0.8,
            fillOpacity: 0.2
        });
        
        if (layer) {
            map._openaipLayers.airports = layer;
        }
    } catch (error) {
        console.error('Erreur lors du chargement des aéroports OpenAIP:', error);
    }
}

/**
 * Charge et affiche des données OpenAIP sur la carte
 * @param {L.Map} map - Instance de la carte Leaflet
 * @param {string} apiUrl - URL de l'API OpenAIP pour récupérer les données
 * @param {Object} options - Options de style pour les données
 */
export async function loadOpenAIPData(map, apiUrl, options = {}) {
    try {
        const response = await fetch(apiUrl);
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(`Erreur HTTP: ${response.status} - ${errorData.error || response.statusText}`);
        }

        const data = await response.json();
        
        // Si la réponse contient une erreur
        if (data.error) {
            throw new Error(data.error);
        }
        
        // Si les données sont vides
        if (!data || (Array.isArray(data) && data.length === 0) || 
            (data.type === 'FeatureCollection' && (!data.features || data.features.length === 0))) {
            console.log('Aucune donnée OpenAIP trouvée pour cette zone');
            return null;
        }

        // Si les données sont au format GeoJSON
        if (data.type === 'FeatureCollection' || data.type === 'Feature') {
            const geoJsonLayer = L.geoJSON(data, {
                style: (feature) => ({
                    color: options.color || '#3388ff',
                    weight: options.weight || 2,
                    opacity: options.opacity || 0.8,
                    fillOpacity: options.fillOpacity || 0.2,
                }),
                onEachFeature: (feature, layer) => {
                    if (feature.properties) {
                        const popupContent = Object.entries(feature.properties)
                            .map(([key, value]) => `<strong>${key}:</strong> ${value}`)
                            .join('<br>');
                        layer.bindPopup(popupContent);
                    }
                }
            }).addTo(map);

            // Ajuster la vue pour afficher toutes les données
            if (geoJsonLayer.getBounds().isValid()) {
                map.fitBounds(geoJsonLayer.getBounds());
            }

            return geoJsonLayer;
        }

        // Si les données sont un tableau d'aéroports ou autres points
        if (Array.isArray(data)) {
            const markers = data.map(item => {
                if (item.latitude && item.longitude) {
                    const marker = L.marker([item.latitude, item.longitude]).addTo(map);
                    
                    // Créer le contenu du popup
                    let popupContent = '';
                    if (item.name) popupContent += `<h3>${item.name}</h3>`;
                    if (item.icao) popupContent += `<p><strong>ICAO:</strong> ${item.icao}</p>`;
                    if (item.elevation) popupContent += `<p><strong>Élévation:</strong> ${item.elevation}m</p>`;
                    
                    if (popupContent) {
                        marker.bindPopup(popupContent);
                    }
                    
                    return marker;
                }
                return null;
            }).filter(m => m !== null);

            // Ajuster la vue si on a des marqueurs
            if (markers.length > 0) {
                const group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds());
            }

            return markers;
        }

    } catch (error) {
        console.error('Erreur lors du chargement des données OpenAIP:', error);
        throw error;
    }
}

/**
 * Ajoute un marqueur personnalisé sur la carte
 * @param {L.Map} map - Instance de la carte Leaflet
 * @param {Array<number>} position - [latitude, longitude]
 * @param {string} title - Titre du marqueur
 * @param {string} description - Description à afficher dans le popup
 */
export function addMarker(map, position, title, description = '') {
    const marker = L.marker(position).addTo(map);
    
    const popupContent = `
        <div>
            <h3 style="margin: 0 0 8px 0; font-weight: bold;">${title}</h3>
            ${description ? `<p style="margin: 0;">${description}</p>` : ''}
        </div>
    `;
    
    marker.bindPopup(popupContent);
    return marker;
}

// Export par défaut pour une utilisation simple
export default {
    initOpenAIPMap,
    loadOpenAIPData,
    addMarker
};
