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
            $table->boolean('paiement_valide')->default(false)->after('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pilotes', function (Blueprint $table) {
            $table->dropColumn('paiement_valide');
        });
    }
};
