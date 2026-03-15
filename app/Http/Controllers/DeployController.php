<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class DeployController extends Controller
{
    private const REPO_URL = 'https://github.com/Cym-Developpement/gliderCup.git';

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
