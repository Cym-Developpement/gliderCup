<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clear {--force : Force la suppression sans confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vide toutes les tables de la base de données';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Êtes-vous sûr de vouloir vider toutes les tables ? Cette action est irréversible.')) {
                $this->info('Opération annulée.');
                return Command::FAILURE;
            }
        }

        try {
            // Désactiver temporairement les vérifications de clés étrangères
            DB::statement('PRAGMA foreign_keys = OFF');

            // Vider les tables dans l'ordre (pivot d'abord, puis les tables principales)
            $this->info('Vidage de la table pilote_planeur...');
            DB::table('pilote_planeur')->delete();

            $this->info('Vidage de la table planeurs...');
            DB::table('planeurs')->delete();

            $this->info('Vidage de la table pilotes...');
            DB::table('pilotes')->delete();

            $this->info('Vidage de la table users...');
            DB::table('users')->delete();

            // Réinitialiser les séquences AUTO_INCREMENT (si nécessaire)
            // Pour SQLite, on peut utiliser DELETE FROM sqlite_sequence
            DB::statement("DELETE FROM sqlite_sequence WHERE name IN ('pilotes', 'planeurs', 'pilote_planeur', 'users')");

            // Réactiver les vérifications de clés étrangères
            DB::statement('PRAGMA foreign_keys = ON');

            $this->info('✓ Toutes les tables ont été vidées avec succès !');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            // Réactiver les vérifications de clés étrangères en cas d'erreur
            DB::statement('PRAGMA foreign_keys = ON');
            
            $this->error('Erreur lors du vidage des tables : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

