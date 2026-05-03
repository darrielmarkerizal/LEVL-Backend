<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('level_configs', function (Blueprint $table) {
            if (Schema::hasColumn('level_configs', 'milestone_badge_id')) {
                $table->dropForeign(['milestone_badge_id']);
                $table->dropColumn('milestone_badge_id');
            }

            if (Schema::hasColumn('level_configs', 'bonus_xp')) {
                $table->dropColumn('bonus_xp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('level_configs', function (Blueprint $table) {
            $table->foreignId('milestone_badge_id')->nullable()->constrained('badges')->nullOnDelete();
            $table->integer('bonus_xp')->default(0);
        });
    }
};
