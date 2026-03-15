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
        Schema::table('planeurs', function (Blueprint $table) {
            $table->string('marque')->nullable()->after('modele');
            $table->string('type')->nullable()->after('marque');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planeurs', function (Blueprint $table) {
            $table->dropColumn(['marque', 'type']);
        });
    }
};
