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
        Schema::table('pilotes', function (Blueprint $table) {
            $table->string('helloasso_checkout_intent_id')->nullable()->after('identifiant_virement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pilotes', function (Blueprint $table) {
            $table->dropColumn('helloasso_checkout_intent_id');
        });
    }
};
