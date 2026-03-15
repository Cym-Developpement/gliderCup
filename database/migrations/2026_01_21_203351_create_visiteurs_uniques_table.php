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
        Schema::create('visiteurs_uniques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->nullable()->constrained('competitions')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->date('date_visite')->index();
            $table->timestamps();
            
            // Index composite pour éviter les doublons (même IP + user agent + compétition + date)
            $table->index(['competition_id', 'ip_address', 'date_visite']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visiteurs_uniques');
    }
};
