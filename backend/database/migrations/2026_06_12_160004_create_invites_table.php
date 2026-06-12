<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            // token statt email ist eindeutig. Frühere UNIQUE-Regel auf email
            // (Befund S7) verhinderte, dieselbe Adresse mehrfach einzuladen.
            $table->string('token')->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['email', 'family_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invites');
    }
};
