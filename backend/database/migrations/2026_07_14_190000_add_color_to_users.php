<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Persönliche Kalenderfarbe (Hex) – gilt für alle Termine des Mitglieds.
        // Nullable: ohne Wahl greift im Frontend ein stabiler ID-basierter Fallback.
        Schema::table('users', function (Blueprint $table) {
            $table->string('color', 7)->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
