<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commentaires_taches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tache_id')->constrained('taches')->onDelete('cascade');
            $table->text('contenu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commentaires_taches');
    }
};
