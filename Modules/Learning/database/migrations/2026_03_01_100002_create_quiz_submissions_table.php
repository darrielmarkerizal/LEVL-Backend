<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments')->onDelete('set null');

            $table->enum('status', ['draft', 'submitted', 'graded', 'late', 'missing'])->default('draft');
            $table->enum('grading_status', ['pending', 'partially_graded', 'waiting_for_grading', 'graded'])->default('pending');

            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('final_score', 8, 2)->nullable();

            $table->json('question_set')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->integer('time_spent_seconds')->nullable();

            $table->integer('attempt_number')->default(1);
            $table->boolean('is_late')->default(false);
            $table->boolean('is_resubmission')->default(false);

            $table->foreignId('previous_submission_id')->nullable()->constrained('quiz_submissions')->onDelete('set null');

            $table->timestamps();

            $table->index(['quiz_id', 'user_id'], 'idx_quiz_submissions_quiz_user');
            $table->index(['user_id', 'status'], 'idx_quiz_submissions_user_status');
            $table->index('grading_status', 'idx_quiz_submissions_grading_status');
            $table->index('enrollment_id', 'idx_quiz_submissions_enrollment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_submissions');
    }
};
