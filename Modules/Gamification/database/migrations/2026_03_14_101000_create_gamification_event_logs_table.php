<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gamification_event_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('event_type', 50);
            $table->string('source_type', 50)->nullable(); // lesson, assignment, course
            $table->unsignedBigInteger('source_id')->nullable();
            $table->json('payload')->nullable(); // limited detail event
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index(['user_id', 'event_type', 'created_at'], 'user_event_created_idx');
            $table->index(['source_type', 'source_id'], 'source_idx');
            $table->index('created_at', 'created_at_idx'); // untuk cleanup old logs
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gamification_event_logs');
    }
};
