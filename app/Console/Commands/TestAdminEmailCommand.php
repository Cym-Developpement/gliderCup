<?php

namespace App\Console\Commands;

use App\Notifications\TestEmailAdmin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class TestAdminEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test-admin {--email= : Adresse email de destination (optionnel, utilise ADMIN_EMAIL par défaut)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie un email de test à l\'administrateur';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email') ?? config('mail.admin_email');

        if (!$email) {
            $this->error('Aucune adresse email configurée. Veuillez définir ADMIN_EMAIL dans le fichier .env ou utiliser l\'option --email.');
            return Command::FAILURE;
        }

        $this->info("Envoi d'un email de test à : {$email}");

        try {
            Notification::route('mail', $email)
                ->notify(new TestEmailAdmin());

            $this->info('✓ Email envoyé avec succès !');
            $this->line("Vérifiez la boîte de réception de : {$email}");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

