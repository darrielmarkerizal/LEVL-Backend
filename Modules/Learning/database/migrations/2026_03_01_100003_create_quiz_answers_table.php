<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_submission_id')->constrained('quiz_submissions')->onDelete('cascade');
            $table->foreignId('quiz_question_id')->constrained('quiz_questions')->onDelete('cascade');

            $table->text('content')->nullable();
            $table->json('selected_options')->nullable();

            $table->decimal('score', 8, 2)->nullable();
            $table->boolean('is_auto_graded')->default(false);
            $table->text('feedback')->nullable();

            $table->timestamps();

            $table->index('quiz_submission_id', 'idx_quiz_answers_submission');
            $table->index('quiz_question_id', 'idx_quiz_answers_question');
            $table->index(['quiz_submission_id', 'quiz_question_id'], 'idx_quiz_answers_composite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
};
