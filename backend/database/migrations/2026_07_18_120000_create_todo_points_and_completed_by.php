<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nest-Blätter (ADR-0026): Punkte-Ledger getrennt von den Todos –
        // Punkte überleben so das Aufräumen/Löschen erledigter Aufgaben.
        Schema::create('todo_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('todo_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('points')->default(1);
            $table->timestamps();

            $table->index(['family_id', 'user_id', 'created_at']);
        });

        // Fürs UI: WER hat abgehakt (Avatar in der Liste).
        Schema::table('todos', function (Blueprint $table) {
            $table->foreignId('completed_by')->nullable()->after('is_done')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable()->after('completed_by');
        });
    }

    public function down(): void
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('completed_by');
            $table->dropColumn('completed_at');
        });
        Schema::dropIfExists('todo_points');
    }
};
