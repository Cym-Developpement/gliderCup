<?php

namespace App\Console\Commands;

use App\Models\Competition;
use Illuminate\Console\Command;

class CreateCompetition extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'competition:create 
                            {--nom=Wassmer Cup : Nom de la compétition}
                            {--description= : Description de la compétition}
                            {--date-debut= : Date de début (format: YYYY-MM-DD)}
                            {--date-fin= : Date de fin (format: YYYY-MM-DD)}
                            {--lieu=Aérodrome de thouars : Lieu de la compétition}
                            {--limite-planeurs=15 : Limite de planeurs}
                            {--actif : Marquer la compétition comme active}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer une nouvelle compétition';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Désactiver toutes les autres compétitions si --actif est utilisé
        if ($this->option('actif')) {
            Competition::where('actif', true)->update(['actif' => false]);
        }

        $competition = Competition::create([
            'nom' => $this->option('nom'),
            'description' => $this->option('description'),
            'date_debut' => $this->option('date-debut') ?: '2026-07-27',
            'date_fin' => $this->option('date-fin') ?: '2026-07-31',
            'lieu' => $this->option('lieu'),
            'limite_planeurs' => (int) $this->option('limite-planeurs'),
            'actif' => $this->option('actif') ? true : false,
        ]);

        $this->info("Compétition créée avec succès !");
        $this->line("ID: {$competition->id}");
        $this->line("Nom: {$competition->nom}");
        $this->line("Dates: {$competition->date_debut->format('d/m/Y')} - {$competition->date_fin->format('d/m/Y')}");
        $this->line("Lieu: {$competition->lieu}");
        $this->line("Limite planeurs: {$competition->limite_planeurs}");
        $this->line("Active: " . ($competition->actif ? 'Oui' : 'Non'));

        return 0;
    }
}
