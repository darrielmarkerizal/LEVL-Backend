<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('assignments', 'max_attempts')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->dropColumn('max_attempts');
            });
        }

        if (Schema::hasColumn('assignments', 'cooldown_minutes')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->dropColumn('cooldown_minutes');
            });
        }

        if (Schema::hasColumn('assignments', 'retake_enabled')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->dropColumn('retake_enabled');
            });
        }

        if (Schema::hasColumn('quizzes', 'max_attempts')) {
            Schema::table('quizzes', function (Blueprint $table) {
                $table->dropColumn('max_attempts');
            });
        }

        if (Schema::hasColumn('quizzes', 'cooldown_minutes')) {
            Schema::table('quizzes', function (Blueprint $table) {
                $table->dropColumn('cooldown_minutes');
            });
        }

        if (Schema::hasColumn('quizzes', 'retake_enabled')) {
            Schema::table('quizzes', function (Blueprint $table) {
                $table->dropColumn('retake_enabled');
            });
        }
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->integer('max_attempts')->nullable();
            $table->integer('cooldown_minutes')->nullable();
            $table->boolean('retake_enabled')->default(false);
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->integer('max_attempts')->nullable();
            $table->integer('cooldown_minutes')->nullable();
            $table->boolean('retake_enabled')->default(false);
        });
    }
};
