<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use ZipArchive;

class BackupCommand extends Command
{
    protected $signature = 'app:backup {--path= : Répertoire de destination du backup}';

    protected $description = 'Crée une sauvegarde ZIP contenant la base de données et les fichiers uploadés';

    public function handle(): int
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$timestamp}.zip";

        $destDir = $this->option('path') ?: storage_path('backups');

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $zipPath = $destDir . '/' . $filename;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Impossible de créer le fichier ZIP : {$zipPath}");
            return self::FAILURE;
        }

        // Base de données SQLite
        $dbPath = database_path('database.sqlite');
        if (file_exists($dbPath)) {
            $this->info('Ajout de la base de données...');
            $zip->addFile($dbPath, 'database.sqlite');
        } else {
            $this->warn('Base de données SQLite introuvable, ignorée.');
        }

        // Fichiers uploadés (storage/app/public)
        $publicStorage = storage_path('app/public');
        if (is_dir($publicStorage)) {
            $this->info('Ajout des fichiers uploadés...');
            $this->addDirectoryToZip($zip, $publicStorage, 'storage');
        }

        // Fichiers privés (storage/app/private)
        $privateStorage = storage_path('app/private');
        if (is_dir($privateStorage)) {
            $this->info('Ajout des fichiers privés...');
            $this->addDirectoryToZip($zip, $privateStorage, 'private');
        }

        $zip->close();

        $size = round(filesize($zipPath) / 1024 / 1024, 2);
        $this->info("Sauvegarde créée : {$zipPath} ({$size} Mo)");

        return self::SUCCESS;
    }

    private function addDirectoryToZip(ZipArchive $zip, string $directory, string $prefix): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relativePath = $prefix . '/' . substr($file->getPathname(), strlen($directory) + 1);
                $zip->addFile($file->getPathname(), $relativePath);
            }
        }
    }
}
