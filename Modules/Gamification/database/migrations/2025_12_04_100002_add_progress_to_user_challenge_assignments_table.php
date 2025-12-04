<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_challenge_assignments', function (Blueprint $table) {
            $table->integer('current_progress')->default(0)->after('status');
            $table->boolean('reward_claimed')->default(false)->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_challenge_assignments', function (Blueprint $table) {
            $table->dropColumn(['current_progress', 'reward_claimed']);
        });
    }
};
