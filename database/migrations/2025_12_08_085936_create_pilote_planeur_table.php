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
        Schema::create('pilote_planeur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pilote_id')->constrained('pilotes')->onDelete('cascade');
            $table->foreignId('planeur_id')->constrained('planeurs')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['pilote_id', 'planeur_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pilote_planeur');
    }
};
