<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badge_rule_cooldowns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('badge_rule_id')->constrained('badge_rules')->cascadeOnDelete();
            $table->timestamp('last_evaluated_at');
            $table->timestamp('can_evaluate_after');
            $table->timestamps();

            $table->unique(['user_id', 'badge_rule_id'], 'user_badge_rule_unique');
            $table->index('can_evaluate_after', 'can_evaluate_after_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_rule_cooldowns');
    }
};
