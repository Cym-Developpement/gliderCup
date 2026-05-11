<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('points_virage_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->onDelete('cascade');
            $table->string('nom', 50);
            $table->string('couleur', 7)->default('#3b82f6');
            $table->timestamps();
            $table->unique(['competition_id', 'nom']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points_virage_tags');
    }
};
