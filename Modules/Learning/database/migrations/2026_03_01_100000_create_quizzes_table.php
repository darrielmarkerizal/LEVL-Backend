<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();

            $table->string('assignable_type')->nullable();
            $table->unsignedBigInteger('assignable_id')->nullable();

            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            $table->string('title', 255);
            $table->text('description')->nullable();

            $table->decimal('passing_grade', 5, 2)->default(75.00);
            $table->boolean('auto_grading')->default(true);
            $table->decimal('max_score', 8, 2)->default(100.00);

            $table->integer('max_attempts')->nullable();
            $table->integer('cooldown_minutes')->default(0);
            $table->integer('time_limit_minutes')->nullable();
            $table->boolean('retake_enabled')->default(false);

            $table->string('randomization_type', 20)->default('static');
            $table->integer('question_bank_count')->nullable();
            $table->string('review_mode', 20)->default('immediate');

            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');

            $table->timestamp('available_from')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->integer('tolerance_minutes')->default(0);

            $table->integer('late_penalty_percent')->default(0);

            $table->timestamps();

            $table->index(['assignable_type', 'assignable_id'], 'idx_quizzes_assignable');
            $table->index(['lesson_id', 'status'], 'idx_quizzes_lesson_status');
            $table->index('status', 'idx_quizzes_status');
            $table->index('created_by', 'idx_quizzes_created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
