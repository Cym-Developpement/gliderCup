<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DeployController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportGpsController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\OpenAIPController;
use App\Http\Controllers\OpenAIPTileController;
use Illuminate\Support\Facades\Route;

// Routes publiques
Route::get('/', [InscriptionController::class, 'index'])
    ->middleware(\App\Http\Middleware\CountHomepageVisit::class)
    ->name('inscription.index');
Route::get('/openaip-map', function () {
    return view('openaip-map');
})->name('openaip-map');
Route::post('/', [InscriptionController::class, 'store'])->name('inscription.store');
Route::get('/paiement/{identifiantVirement?}', [\App\Http\Controllers\PaiementController::class, 'index'])->name('paiement.public');
Route::get('/paiement/{identifiantVirement}/validation', [\App\Http\Controllers\PaiementController::class, 'validation'])->name('paiement.validation');
Route::get('/paiement/{identifiantVirement}/erreur', [\App\Http\Controllers\PaiementController::class, 'erreur'])->name('paiement.erreur');
Route::get('/api/paiement/check/{checkoutIntentId?}', [\App\Http\Controllers\PaiementController::class, 'checkPaiement'])->name('paiement.check');
Route::post('/api/paiement/check', [\App\Http\Controllers\PaiementController::class, 'checkPaiement'])->name('paiement.check.post');
Route::get('/reglement', [InscriptionController::class, 'reglement'])->name('reglement.public');
Route::post('/contact', [\App\Http\Controllers\ContactController::class, 'send'])->name('contact.send');
Route::get('/api/openaip/data', [OpenAIPController::class, 'getData'])->name('openaip.data');
Route::get('/api/openaip/tiles/{z}/{x}/{y}.png', [OpenAIPTileController::class, 'getTile'])->name('openaip.tiles');
Route::get('/airport/{icao}', [\App\Http\Controllers\InscriptionController::class, 'getAirportData'])->name('airport.data');
Route::get('/img/marker/{numero}', function ($numero) {
    $svg = file_get_contents(public_path('img/path1.svg'));
    $svg = str_replace('#PP', $numero, $svg);
    return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
})->where('numero', '[0-9]+')->name('marker.svg');

// Export GPS des points de virage
Route::get('/export/gps/{format}', [ExportGpsController::class, 'export'])->name('export.gps');

// Route de déploiement (GET manuel + POST webhook GitHub)
Route::match(['get', 'post'], '/update', [DeployController::class, 'update'])->name('deploy.update');

// Routes d'authentification
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Routes de réinitialisation de mot de passe
Route::get('/password/reset', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');

// Routes protégées - Participants
Route::middleware(['auth:pilotes'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/upload-documents', [DashboardController::class, 'uploadDocuments'])->name('dashboard.upload-documents');
    Route::post('/dashboard/planeurs/{id}/upload-documents', [DashboardController::class, 'uploadDocumentsPlaneur'])->name('dashboard.upload-documents-planeur');
    Route::get('/paiement', [\App\Http\Controllers\PaiementController::class, 'index'])->name('paiement.index');
});

// Routes protégées - Administrateurs
Route::middleware(['auth:web', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/planeurs', [AdminController::class, 'listePlaneurs'])->name('planeurs');
    Route::get('/pilotes/{id}/details', [AdminController::class, 'getPiloteDetails'])->name('pilotes.details');
    Route::post('/pilotes/{id}/update', [AdminController::class, 'updatePilote'])->name('pilotes.update');
    Route::post('/pilotes/{id}/update-montant', [AdminController::class, 'updateMontantPilote'])->name('pilotes.update-montant');
    Route::post('/pilotes/{id}/envoyer-message-compte-cree', [AdminController::class, 'envoyerMessageCompteCree'])->name('pilotes.envoyer-message-compte-cree');
    Route::post('/pilotes/{id}/remplacer-document', [AdminController::class, 'remplacerDocument'])->name('pilotes.remplacer-document');
    Route::post('/inscriptions/{id}/valider', [AdminController::class, 'validerInscription'])->name('inscriptions.valider');
    Route::post('/inscriptions/{id}/refuser', [AdminController::class, 'refuserInscription'])->name('inscriptions.refuser');
    Route::delete('/inscriptions/{id}/supprimer', [AdminController::class, 'supprimerInscription'])->name('inscriptions.supprimer');
    Route::get('/export/pilotes', [AdminController::class, 'exporterPilotes'])->name('export.pilotes');
    Route::get('/export/planeurs', [AdminController::class, 'exporterPlaneurs'])->name('export.planeurs');
    Route::get('/paiement/configuration', [AdminController::class, 'getPaiementConfiguration'])->name('paiement.configuration');
    Route::post('/paiement/configuration', [AdminController::class, 'updatePaiementConfiguration'])->name('paiement.configuration.update');
    Route::post('/paiement/{id}/valider', [AdminController::class, 'validerPaiement'])->name('paiement.valider');
    Route::post('/paiement/{id}/invalider', [AdminController::class, 'invaliderPaiement'])->name('paiement.invalider');
    Route::post('/paiement/{id}/envoyer-lien', [AdminController::class, 'envoyerLienPaiement'])->name('paiement.envoyer-lien');
    Route::get('/pilotes/{id}/messages', [AdminController::class, 'getMessages'])->name('pilotes.messages');
    Route::post('/pilotes/{id}/messages', [AdminController::class, 'envoyerMessage'])->name('pilotes.messages.envoyer');
    Route::post('/messages/envoyer-groupe', [AdminController::class, 'envoyerMessageGroupe'])->name('messages.envoyer-groupe');
    Route::get('/messages/groupes', [AdminController::class, 'listeMessagesGroupes'])->name('messages.groupes');
    Route::post('/competition/reglement', [AdminController::class, 'updateReglement'])->name('competition.reglement.update');
    Route::post('/competition/code-aeroport', [AdminController::class, 'updateCodeAeroport'])->name('competition.code-aeroport.update');
    Route::get('/points-virage', [AdminController::class, 'getPointsVirage'])->name('points-virage.index');
    Route::post('/points-virage/{id?}', [AdminController::class, 'savePointVirage'])->name('points-virage.save');
    Route::delete('/points-virage/{id}', [AdminController::class, 'deletePointVirage'])->name('points-virage.delete');
    Route::post('/competition/search-airport', [AdminController::class, 'searchAirport'])->name('competition.search-airport');
    Route::get('/competition/airport-data/{icao}', [AdminController::class, 'getAirportData'])->name('competition.airport-data');
    Route::get('/paiement/test', [\App\Http\Controllers\PaiementController::class, 'test'])->name('paiement.test');
    Route::get('/paiement/verifier-tous', [\App\Http\Controllers\PaiementController::class, 'verifierTousLesPaiements'])->name('paiement.verifier-tous');
    Route::post('/contact/{id}/repondre', [AdminController::class, 'repondreMessageContact'])->name('contact.repondre');
    Route::get('/taches', [AdminController::class, 'getTaches'])->name('taches.index');
    Route::post('/taches/{id?}', [AdminController::class, 'saveTache'])->name('taches.save');
    Route::delete('/taches/{id}', [AdminController::class, 'deleteTache'])->name('taches.delete');

    // Gestion des administrateurs
    Route::get('/admins', [AdminController::class, 'getAdmins'])->name('admins.index');
    Route::post('/admins/{id?}', [AdminController::class, 'saveAdmin'])->name('admins.save');
    Route::delete('/admins/{id}', [AdminController::class, 'deleteAdmin'])->name('admins.delete');
});
