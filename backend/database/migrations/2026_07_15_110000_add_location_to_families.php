<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Heimatort der Familie fürs Wetter-Widget: von Verwaltern gepflegt,
        // alle Mitglieder sehen dasselbe Zuhause-Wetter (Open-Meteo, Frontend).
        Schema::table('families', function (Blueprint $table) {
            $table->string('location_name')->nullable()->after('name');
            $table->decimal('latitude', 8, 5)->nullable()->after('location_name');
            $table->decimal('longitude', 8, 5)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('families', function (Blueprint $table) {
            $table->dropColumn(['location_name', 'latitude', 'longitude']);
        });
    }
};
