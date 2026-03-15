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
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('email');
            $table->text('message');
            $table->boolean('repondu')->default(false);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('reponse')->nullable();
            $table->timestamp('reponse_envoyee_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
