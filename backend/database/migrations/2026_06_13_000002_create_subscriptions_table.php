<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Abo pro Familie (ein Abo deckt die ganze Familie ab, ADR-0013).
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('plan')->default('premium');
            $table->string('status')->default('active'); // active | canceled
            // Bezahlanbieter – aktuell "manual" (Platzhalter bis echtes IAP via
            // Apple/Google bzw. Stripe, siehe ADR-0013).
            $table->string('provider')->default('manual');
            $table->string('provider_ref')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
