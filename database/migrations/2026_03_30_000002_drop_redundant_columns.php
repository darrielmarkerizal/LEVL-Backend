<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'tags_json')) {
                $table->dropColumn('tags_json');
            }
            if (Schema::hasColumn('courses', 'prereq_json')) {
                $table->dropColumn('prereq_json');
            }
        });

        Schema::table('user_gamification_stats', function (Blueprint $table) {
            if (Schema::hasColumn('user_gamification_stats', 'completed_challenges')) {
                $table->dropColumn('completed_challenges');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'tags_json')) {
                $table->json('tags_json')->nullable();
            }
            if (! Schema::hasColumn('courses', 'prereq_json')) {
                $table->json('prereq_json')->nullable();
            }
        });

        Schema::table('user_gamification_stats', function (Blueprint $table) {
            if (! Schema::hasColumn('user_gamification_stats', 'completed_challenges')) {
                $table->unsignedInteger('completed_challenges')->default(0);
            }
        });
    }
};
