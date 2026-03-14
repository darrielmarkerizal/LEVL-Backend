<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badge_rules', function (Blueprint $table) {
            $table->boolean('rule_enabled')->default(true)->after('priority');
            
            $table->index('rule_enabled', 'rule_enabled_idx');
        });
    }

    public function down(): void
    {
        Schema::table('badge_rules', function (Blueprint $table) {
            $table->dropIndex('rule_enabled_idx');
            $table->dropColumn('rule_enabled');
        });
    }
};
