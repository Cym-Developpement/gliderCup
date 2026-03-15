<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Limite de planeurs
    |--------------------------------------------------------------------------
    |
    | Nombre maximum de planeurs autorisés à s'inscrire à l'événement.
    | Cette valeur peut être modifiée dans le fichier .env avec la variable
    | EVENT_MAX_PLANEURS.
    |
    */

    'max_planeurs' => env('EVENT_MAX_PLANEURS', 15),
];

