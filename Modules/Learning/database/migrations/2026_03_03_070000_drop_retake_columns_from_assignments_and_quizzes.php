<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assignments')) {
            Schema::table('assignments', function (Blueprint $table) {
                $columns = ['retake_enabled', 'max_attempts', 'cooldown_minutes'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('assignments', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('quizzes')) {
            Schema::table('quizzes', function (Blueprint $table) {
                $columns = ['retake_enabled', 'max_attempts', 'cooldown_minutes'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('quizzes', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assignments')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->integer('max_attempts')->nullable()->after('tolerance_minutes');
                $table->integer('cooldown_minutes')->default(0)->after('max_attempts');
                $table->boolean('retake_enabled')->default(false)->after('cooldown_minutes');
            });
        }

        if (Schema::hasTable('quizzes')) {
            Schema::table('quizzes', function (Blueprint $table) {
                $table->integer('max_attempts')->nullable()->after('max_score');
                $table->integer('cooldown_minutes')->default(0)->after('max_attempts');
                $table->boolean('retake_enabled')->default(false)->after('cooldown_minutes');
            });
        }
    }
};
