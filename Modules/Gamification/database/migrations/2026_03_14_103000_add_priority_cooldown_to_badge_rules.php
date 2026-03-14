<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badge_rules', function (Blueprint $table) {
            $table->integer('priority')->default(0)->after('conditions'); // higher = more important
            $table->integer('cooldown_seconds')->nullable()->after('priority'); // prevent spam
            $table->string('progress_window', 20)->nullable()->after('cooldown_seconds'); // daily, weekly, monthly, lifetime

            $table->index('priority', 'priority_idx');
        });
    }

    public function down(): void
    {
        Schema::table('badge_rules', function (Blueprint $table) {
            $table->dropIndex('priority_idx');
            $table->dropColumn(['priority', 'cooldown_seconds', 'progress_window']);
        });
    }
};
