<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('assignments', 'allow_resubmit')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->dropColumn('allow_resubmit');
            });
        }

        if (Schema::hasColumn('quizzes', 'allow_resubmit')) {
            Schema::table('quizzes', function (Blueprint $table) {
                $table->dropColumn('allow_resubmit');
            });
        }

        if (Schema::hasColumn('submissions', 'is_resubmission')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->dropColumn('is_resubmission');
            });
        }

        if (Schema::hasColumn('submissions', 'previous_submission_id')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->dropColumn('previous_submission_id');
            });
        }

        if (Schema::hasColumn('quiz_submissions', 'is_resubmission')) {
            Schema::table('quiz_submissions', function (Blueprint $table) {
                $table->dropColumn('is_resubmission');
            });
        }

        Schema::dropIfExists('overrides');
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->boolean('allow_resubmit')->nullable();
            $table->integer('late_penalty_percent')->nullable();
            $table->integer('tolerance_minutes')->default(0);
            $table->timestamp('available_from')->nullable();
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->boolean('allow_resubmit')->nullable();
            $table->integer('late_penalty_percent')->default(0);
            $table->integer('tolerance_minutes')->default(0);
            $table->timestamp('available_from')->nullable();
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->boolean('is_late')->default(false);
            $table->boolean('is_resubmission')->default(false);
            $table->integer('attempt_number')->default(1);
            $table->foreignId('previous_submission_id')->nullable()->constrained('submissions');
        });

        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->boolean('is_late')->default(false);
            $table->boolean('is_resubmission')->default(false);
            $table->integer('attempt_number')->default(1);
        });
    }
};
