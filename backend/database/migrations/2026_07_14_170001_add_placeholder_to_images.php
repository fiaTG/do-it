<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Winziger Base64-Platzhalter (LQIP) fürs Blur-up im Galerie-Grid,
        // erzeugt vom GenerateThumbnail-Job. Nullable: Altbestand hat keinen.
        Schema::table('images', function (Blueprint $table) {
            $table->text('placeholder')->nullable()->after('thumbnail_path');
        });
    }

    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn('placeholder');
        });
    }
};
