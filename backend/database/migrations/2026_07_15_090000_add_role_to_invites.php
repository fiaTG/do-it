<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rolle des Eingeladenen wird schon bei der Einladung festgelegt
        // (ADR-0021, löst offenen Punkt aus ADR-0019).
        Schema::table('invites', function (Blueprint $table) {
            $table->string('role')->default('guardian')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('invites', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
