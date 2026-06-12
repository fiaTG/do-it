<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Auswählbare Dashboard-Apps. Die frühere Spalte appPfad (hartkodierte
        // Localhost-URLs, Befund B3) entfällt – die Verlinkung läuft über den
        // Slug bzw. benannte Frontend-Routen.
        Schema::create('apps', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apps');
    }
};
