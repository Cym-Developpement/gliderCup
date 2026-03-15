<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class DeployController extends Controller
{
    private const REPO_URL = 'https://github.com/Cym-Developpement/gliderCup.git';

    /**
     * Détermine le chemin du binaire PHP CLI.
     */
    private function getPhpBinary(): string
    {
        // Si PHP_BINARY n'est pas fpm/cgi, on l'utilise directement
        if (!preg_match('/(fpm|cgi)/', PHP_BINARY) && is_executable(PHP_BINARY)) {
            return PHP_BINARY;
        }

        // Chercher le CLI dans le même répertoire que le binaire actuel
        // ex: /usr/bin/php-fpm8.2 → /usr/bin/php8.2, /usr/bin/php
        $dir = PHP_BINDIR;
        $version = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;

        $candidates = [
            $dir . '/php' . $version,
            $dir . '/php' . PHP_MAJOR_VERSION,
            $dir . '/php',
        ];

        foreach ($candidates as $candidate) {
            if (is_executable($candidate)) {
                return $candidate;
            }
        }

        // Dernier recours
        return 'php';
    }

    public function update(Request $request): JsonResponse
    {
        // Vérifier la signature GitHub sur les requêtes POST
        if ($request->isMethod('post')) {
            $secret = config('app.deploy_webhook_secret');
            $signature = $request->header('X-Hub-Signature-256');

            if (!$secret || !$signature) {
                return response()->json(['status' => 'error', 'output' => 'Signature manquante'], 403);
            }

            $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);

            if (!hash_equals($expected, $signature)) {
                return response()->json(['status' => 'error', 'output' => 'Signature invalide'], 403);
            }
        }

        $basePath = base_path();

        // Si pas de dépôt git, initialiser et rattacher au remote
        if (!is_dir($basePath . '/.git')) {
            $init = $this->initRepository($basePath);
            if ($init['status'] === 'error') {
                return response()->json($init, 500);
            }
        }

        // Réinitialiser les modifications locales avant le pull
        $reset = new Process(['git', 'checkout', '.'], $basePath);
        $reset->setTimeout(30);
        $reset->run();

        $process = new Process(['git', 'pull'], $basePath);
        $process->setTimeout(60);
        $process->run();

        if ($process->isSuccessful()) {
            $pullOutput = $process->getOutput();

            // Sauvegarder la base de données avant la migration
            $backupMessage = null;
            $dbPath = database_path('database.sqlite');
            if (file_exists($dbPath)) {
                $backupDir = storage_path('backup');
                if (!is_dir($backupDir)) {
                    mkdir($backupDir, 0755, true);
                }
                $backupFile = $backupDir . '/database_' . date('Y-m-d_H-i-s') . '.sqlite';
                if (copy($dbPath, $backupFile)) {
                    $backupMessage = 'Backup créé : ' . basename($backupFile);
                } else {
                    $backupMessage = 'Erreur lors de la création du backup';
                }
            }

            // Créer le lien symbolique storage si nécessaire
            if (!file_exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }

            // Installer composer.phar si absent et lancer composer update
            $composerOutput = null;
            $composerPhar = $basePath . '/composer.phar';
            if (!file_exists($composerPhar)) {
                // Téléchargement direct du phar
                $phar = @file_get_contents('https://getcomposer.org/composer-stable.phar');
                if ($phar !== false) {
                    file_put_contents($composerPhar, $phar);
                    chmod($composerPhar, 0755);
                } else {
                    $composerOutput = 'Erreur : impossible de télécharger composer.phar';
                }
            }

            if (file_exists($composerPhar)) {
                // Vider le cache composer pour forcer la résolution des nouvelles versions
                $clearCache = new Process([$this->getPhpBinary(), 'composer.phar', 'clear-cache', '--no-interaction'], $basePath);
                $clearCache->setEnv(['HOME' => $basePath, 'COMPOSER_HOME' => $basePath . '/.composer']);
                $clearCache->setTimeout(30);
                $clearCache->run();

                $composer = new Process([$this->getPhpBinary(), 'composer.phar', 'update', '--no-dev', '--no-interaction', '--optimize-autoloader', '--ignore-platform-reqs'], $basePath);
                $composer->setEnv(['HOME' => $basePath, 'COMPOSER_HOME' => $basePath . '/.composer']);
                $composer->setTimeout(300);
                $composer->run();
                $composerOutput = $composer->isSuccessful() ? $composer->getOutput() : 'Erreur composer : ' . $composer->getErrorOutput();
            }

            // Vider le cache après composer update
            $cacheFiles = [
                $basePath . '/bootstrap/cache/packages.php',
                $basePath . '/bootstrap/cache/services.php',
            ];
            foreach ($cacheFiles as $cacheFile) {
                if (file_exists($cacheFile)) {
                    @unlink($cacheFile);
                }
            }

            // Lancer les migrations directement dans le processus PHP courant
            try {
                Artisan::call('migrate', ['--force' => true]);
                $migrateOutput = Artisan::output();
            } catch (\Exception $e) {
                $migrateOutput = 'Erreur migration : ' . $e->getMessage();
            }

            return response()->json([
                'status' => 'success',
                'output' => $pullOutput,
                'backup' => $backupMessage,
                'composer' => $composerOutput,
                'migrate' => $migrateOutput,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'output' => $process->getErrorOutput(),
        ], 500);
    }

    private function initRepository(string $basePath): array
    {
        $commands = [
            ['git', 'init'],
            ['git', 'remote', 'add', 'origin', self::REPO_URL],
            ['git', 'fetch', 'origin'],
            ['git', 'checkout', '-b', 'main'],
            ['git', 'reset', 'origin/main'],
            ['git', 'branch', '--set-upstream-to=origin/main', 'main'],
        ];

        foreach ($commands as $cmd) {
            $process = new Process($cmd, $basePath);
            $process->setTimeout(60);
            $process->run();

            if (!$process->isSuccessful()) {
                return [
                    'status' => 'error',
                    'step' => implode(' ', $cmd),
                    'output' => $process->getErrorOutput(),
                ];
            }
        }

        return ['status' => 'success'];
    }
}
