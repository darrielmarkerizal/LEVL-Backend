<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('user_challenge_completions');
        Schema::dropIfExists('user_challenge_assignments');
        Schema::dropIfExists('challenges');
    }

    public function down(): void
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type');
            $table->json('criteria')->nullable();
            $table->integer('target_count')->default(1);
            $table->integer('points_reward')->default(0);
            $table->foreignId('badge_id')->nullable()->constrained('badges')->nullOnDelete();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_challenge_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('challenge_id')->constrained('challenges')->cascadeOnDelete();
            $table->date('assigned_date');
            $table->string('status')->default('pending');
            $table->integer('current_progress')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->boolean('reward_claimed')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_challenge_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('challenge_id')->constrained('challenges')->cascadeOnDelete();
            $table->date('completed_date');
            $table->integer('xp_earned')->default(0);
            $table->json('completion_data')->nullable();
            $table->timestamps();
        });
    }
};
