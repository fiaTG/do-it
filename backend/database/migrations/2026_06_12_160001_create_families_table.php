<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Fremdschlüssel users.family_id -> families.id nachreichen.
        // Wird die Familie gelöscht, bleibt der Nutzer bestehen (family_id = null).
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('family_id')
                ->references('id')->on('families')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['family_id']);
        });

        Schema::dropIfExists('families');
    }
};
