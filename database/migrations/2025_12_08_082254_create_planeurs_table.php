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
        Schema::create('planeurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pilote_id')->constrained('pilotes')->onDelete('cascade');
            $table->string('modele');
            $table->string('immatriculation')->unique();
            $table->string('classe')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planeurs');
    }
};
