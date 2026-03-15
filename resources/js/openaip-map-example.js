import { initOpenAIPMap } from './openaip-map.js';

// Initialiser la carte
const map = initOpenAIPMap('openaip-map', {
    center: [46.6, 2.5], // Centre de la France
    zoom: 6
});

// Fonctions pour les boutons
window.centerOnFrance = function() {
    map.setView([46.6, 2.5], 6);
};

// Fonction pour basculer l'affichage des tuiles OpenAIP
window.toggleOpenAIPTiles = function() {
    if (map._baseLayers && map._baseLayers.openaip) {
        if (map.hasLayer(map._baseLayers.openaip)) {
            map.removeLayer(map._baseLayers.openaip);
            console.log('Tuiles OpenAIP désactivées');
        } else {
            map.addLayer(map._baseLayers.openaip);
            console.log('Tuiles OpenAIP activées');
        }
    }
};
