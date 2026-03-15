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
        // Cette migration est déjà gérée par add_user_id_to_pilotes_table
        // On la laisse vide pour éviter les conflits
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
