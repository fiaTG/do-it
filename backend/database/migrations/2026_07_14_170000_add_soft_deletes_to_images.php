<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Papierkorb (ADR-0020): Löschen ist ab jetzt ein Soft-Delete, die
        // Dateien bleiben bis zum Purge (30 Tage) im Storage erhalten.
        Schema::table('images', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
