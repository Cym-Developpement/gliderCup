<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pilotes', function (Blueprint $table) {
            $table->decimal('montant_custom', 8, 2)->nullable()->after('paiement_valide');
        });
    }

    public function down(): void
    {
        Schema::table('pilotes', function (Blueprint $table) {
            $table->dropColumn('montant_custom');
        });
    }
};
