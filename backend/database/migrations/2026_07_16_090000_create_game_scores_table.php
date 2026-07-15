<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fun Area: Highscores je Familie und Spiel (Produkt-Backlog).
        // Jede Runde wird gespeichert; die Bestenliste aggregiert per MAX(score).
        Schema::create('game_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('game');
            $table->unsignedInteger('score');
            $table->timestamps();

            $table->index(['family_id', 'game', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_scores');
    }
};
