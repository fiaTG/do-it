<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // n:m – welche Apps ein Nutzer auf seinem Dashboard hat (vormals userapps).
        Schema::create('app_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('app_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['user_id', 'app_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_user');
    }
};
