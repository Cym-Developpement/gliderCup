<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel...
$app = require_once __DIR__.'/../bootstrap/app.php';

// Vérifier et exécuter les migrations en attente automatiquement
try {
    // Exécuter les migrations en attente (ne fait rien si tout est à jour)
    // --force : exécute sans confirmation (nécessaire en environnement non-interactif)
    Artisan::call('migrate', ['--force' => true]);
} catch (\Exception $e) {
    // En cas d'erreur, continuer quand même (ne pas bloquer l'application)
    // Les erreurs seront loggées par Laravel dans storage/logs/laravel.log
    // En production, vous pouvez vouloir logger l'erreur ici
}

// Handle the request...
$app->handleRequest(Request::capture());
