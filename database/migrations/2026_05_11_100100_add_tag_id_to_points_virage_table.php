<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('points_virage', function (Blueprint $table) {
            $table->foreignId('tag_id')
                ->nullable()
                ->after('points')
                ->constrained('points_virage_tags')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('points_virage', function (Blueprint $table) {
            $table->dropForeign(['tag_id']);
            $table->dropColumn('tag_id');
        });
    }
};
