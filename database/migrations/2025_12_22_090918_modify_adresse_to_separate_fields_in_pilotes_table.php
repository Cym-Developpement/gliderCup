<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pilotes', function (Blueprint $table) {
            // Supprimer l'ancienne colonne adresse (text)
            $table->dropColumn('adresse');
            
            // Ajouter les trois nouvelles colonnes
            $table->string('adresse')->nullable()->after('club');
            $table->string('code_postal', 10)->nullable()->after('adresse');
            $table->string('ville')->nullable()->after('code_postal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pilotes', function (Blueprint $table) {
            // Supprimer les trois colonnes séparées
            $table->dropColumn(['adresse', 'code_postal', 'ville']);
            
            // Restaurer l'ancienne colonne adresse (text)
            $table->text('adresse')->nullable()->after('club');
        });
    }
};
