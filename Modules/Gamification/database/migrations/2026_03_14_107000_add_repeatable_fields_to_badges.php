<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->boolean('is_repeatable')->default(false)->after('threshold');
            $table->integer('max_awards_per_user')->nullable()->after('is_repeatable');

            $table->index('is_repeatable', 'is_repeatable_idx');
        });
    }

    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropIndex('is_repeatable_idx');
            $table->dropColumn(['is_repeatable', 'max_awards_per_user']);
        });
    }
};
