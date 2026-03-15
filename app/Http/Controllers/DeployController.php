<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class DeployController extends Controller
{
    private const REPO_URL = 'https://github.com/Cym-Developpement/gliderCup.git';

    public function update(Request $request): JsonResponse
    {
        $basePath = base_path();

        // Si pas de dépôt git, initialiser et rattacher au remote
        if (!is_dir($basePath . '/.git')) {
            $init = $this->initRepository($basePath);
            if ($init['status'] === 'error') {
                return response()->json($init, 500);
            }
        }

        $process = new Process(['git', 'pull'], $basePath);
        $process->setTimeout(60);
        $process->run();

        if ($process->isSuccessful()) {
            return response()->json([
                'status' => 'success',
                'output' => $process->getOutput(),
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
