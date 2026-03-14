<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_event_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('event_type', 50); // lesson_completed, assignment_submitted, etc.
            $table->string('scope_type', 50)->nullable(); // course, unit, global
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->integer('counter')->default(0);
            $table->string('window', 20)->default('lifetime'); // daily, weekly, monthly, lifetime
            $table->date('window_start')->nullable();
            $table->date('window_end')->nullable();
            $table->timestamp('last_increment_at')->nullable();
            $table->timestamps();
            
            // Composite unique untuk prevent duplicate
            $table->unique(
                ['user_id', 'event_type', 'scope_type', 'scope_id', 'window', 'window_start'], 
                'user_event_counter_unique'
            );
            
            // Indexes untuk fast lookup
            $table->index(['user_id', 'event_type', 'window'], 'user_event_type_window_idx');
            $table->index('window_end', 'window_end_idx'); // untuk cleanup expired windows
            $table->index(['event_type', 'window'], 'event_type_window_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_event_counters');
    }
};
