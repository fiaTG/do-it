<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * owner_id = Familienmitglied, FÜR das der Termin ist (Familienkalender:
     * Farbe/Zuordnung nach Person). Standard = Ersteller (user_id).
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->after('user_id')
                ->constrained('users')->nullOnDelete();
        });

        // Bestehende Termine: Owner = Ersteller.
        DB::table('events')->whereNull('owner_id')->update([
            'owner_id' => DB::raw('user_id'),
        ]);
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
        });
    }
};
