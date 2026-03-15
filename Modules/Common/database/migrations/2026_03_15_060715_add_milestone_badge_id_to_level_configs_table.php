<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('level_configs', function (Blueprint $table) {
            $table->foreignId('milestone_badge_id')->nullable()->after('rewards')->constrained('badges')->nullOnDelete();
            $table->integer('bonus_xp')->default(0)->after('milestone_badge_id')->comment('Bonus XP awarded when reaching this level');
        });
    }

    public function down(): void
    {
        Schema::table('level_configs', function (Blueprint $table) {
            $table->dropForeign(['milestone_badge_id']);
            $table->dropColumn(['milestone_badge_id', 'bonus_xp']);
        });
    }
};
