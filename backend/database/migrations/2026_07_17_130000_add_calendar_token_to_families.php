<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kalender-Freigabe (ADR-0024, Premium): geheimes Token für die
        // .ics-Abo-URL der Familie. null = Freigabe nicht aktiv.
        Schema::table('families', function (Blueprint $table) {
            $table->string('calendar_token', 64)->nullable()->unique()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('families', function (Blueprint $table) {
            $table->dropColumn('calendar_token');
        });
    }
};
