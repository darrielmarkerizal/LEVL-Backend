<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            
            $table->string('category', 50)->nullable()->after('type');

            
            $table->enum('rarity', ['common', 'uncommon', 'rare', 'epic', 'legendary'])
                ->default('common')
                ->after('category');

            
            $table->integer('xp_reward')->default(0)->after('rarity');

            
            $table->boolean('active')->default(true)->after('xp_reward');

            
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
