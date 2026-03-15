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
            $table->string('cdn_cen')->nullable()->after('statut');
            $table->string('responsabilite_civile')->nullable()->after('cdn_cen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planeurs', function (Blueprint $table) {
            $table->dropColumn(['cdn_cen', 'responsabilite_civile']);
        });
    }
};
