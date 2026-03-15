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
            $table->string('feuille_declarative_qualifications')->nullable()->after('autorisation_parentale');
            $table->string('visite_medicale_classe_2')->nullable()->after('feuille_declarative_qualifications');
            $table->string('spl_valide')->nullable()->after('visite_medicale_classe_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pilotes', function (Blueprint $table) {
            $table->dropColumn(['feuille_declarative_qualifications', 'visite_medicale_classe_2', 'spl_valide']);
        });
    }
};
