<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('paiement_configurations', function (Blueprint $table) {
            $table->id();
            $table->text('adresse_cheque')->nullable();
            $table->string('iban_virement')->nullable();
            $table->string('bic_virement')->nullable();
            $table->string('helloasso_checkout_url')->nullable();
            $table->timestamps();
        });

        // Créer une configuration par défaut vide
        DB::table('paiement_configurations')->insert([
            'adresse_cheque' => env('PAIEMENT_CHEQUE_ADRESSE', ''),
            'iban_virement' => env('PAIEMENT_VIREMENT_IBAN', ''),
            'bic_virement' => env('PAIEMENT_VIREMENT_BIC', ''),
            'helloasso_checkout_url' => env('HELLOASSO_CHECKOUT_URL', ''),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiement_configurations');
    }
};
