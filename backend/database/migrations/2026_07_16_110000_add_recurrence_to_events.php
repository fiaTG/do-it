<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Wiederkehrende Termine (Produkt-Backlog: Mülltonnen wöchentlich,
        // TÜV jährlich). Gespeichert wird nur die Serie; die Vorkommen
        // expandiert das Frontend (lib/recurrence.ts).
        Schema::table('events', function (Blueprint $table) {
            $table->string('recurrence')->nullable()->after('ends_at');
            $table->date('recurrence_until')->nullable()->after('recurrence');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['recurrence', 'recurrence_until']);
        });
    }
};
