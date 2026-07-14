<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Für Datums-Gruppierung (Aufnahme- statt Upload-Datum) und ein
        // seitenverhältnis-treues Galerie-Layout (kein erzwungener Square-Crop).
        Schema::table('images', function (Blueprint $table) {
            $table->timestamp('taken_at')->nullable()->after('thumbnail_path');
            $table->unsignedInteger('width')->nullable()->after('taken_at');
            $table->unsignedInteger('height')->nullable()->after('width');
        });
    }

    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn(['taken_at', 'width', 'height']);
        });
    }
};
