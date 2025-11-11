<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lessons')) {
            Schema::table('lessons', function (Blueprint $table) {

                $table->dropUnique(['slug']);

                if (! Schema::hasColumn('lessons', 'status')) {
                    $table->enum('status', ['draft', 'published'])->default('draft')->after('description');
                }
                if (! Schema::hasColumn('lessons', 'published_at')) {
                    $table->timestamp('published_at')->nullable()->after('status');
                }

                if (Schema::hasColumn('lessons', 'estimated_duration') && ! Schema::hasColumn('lessons', 'duration_minutes')) {
                    $table->renameColumn('estimated_duration', 'duration_minutes');
                }

                $table->unique(['unit_id', 'slug'], 'lessons_unit_id_slug_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lessons')) {
            Schema::table('lessons', function (Blueprint $table) {

                $table->dropUnique('lessons_unit_id_slug_unique');

                if (Schema::hasColumn('lessons', 'duration_minutes') && ! Schema::hasColumn('lessons', 'estimated_duration')) {
                    $table->renameColumn('duration_minutes', 'estimated_duration');
                }

                if (Schema::hasColumn('lessons', 'published_at')) {
                    $table->dropColumn('published_at');
                }
                if (Schema::hasColumn('lessons', 'status')) {
                    $table->dropColumn('status');
                }

                $table->unique('slug');
            });
        }
    }
};
