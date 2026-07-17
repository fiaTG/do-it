<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kalender-Abos (ADR-0023, Premium): externe iCal-Kalender (Schule,
        // Verein, Abfallkalender) als schreibgeschützte Ebene NEBEN den
        // Familien-Terminen – kein Import in die events-Tabelle.
        Schema::create('calendar_feeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('color', 7); // Hex-Farbe fürs Kalender-Layer
            // null = einmaliger Datei-Import (.ics hochgeladen, kein Refresh).
            $table->string('url', 2048)->nullable();
            // Zuletzt geholter/hochgeladener ICS-Rohtext: dient als Cache und
            // hält die Termine auch, wenn der Anbieter gerade nicht erreichbar ist.
            $table->mediumText('ics_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_error')->nullable();
            $table->timestamps();

            $table->index('family_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_feeds');
    }
};
