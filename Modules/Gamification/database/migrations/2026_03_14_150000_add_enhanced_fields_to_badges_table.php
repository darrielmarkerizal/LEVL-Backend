<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            // Add category field (optional, for grouping badges)
            $table->string('category', 50)->nullable()->after('type');

            // Add rarity field (common, uncommon, rare, epic, legendary)
            $table->enum('rarity', ['common', 'uncommon', 'rare', 'epic', 'legendary'])
                ->default('common')
                ->after('category');

            // Add XP reward (bonus XP when badge is awarded)
            $table->integer('xp_reward')->default(0)->after('rarity');

            // Add active status (to enable/disable badge)
            $table->boolean('active')->default(true)->after('xp_reward');

            // Add indexes for performance
            $table->index('category');
            $table->index('rarity');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropIndex(['rarity']);
            $table->dropIndex(['active']);
            $table->dropColumn(['category', 'rarity', 'xp_reward', 'active']);
        });
    }
};
