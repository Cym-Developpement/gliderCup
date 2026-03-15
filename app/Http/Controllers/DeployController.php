<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class DeployController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $basePath = base_path();

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
}
