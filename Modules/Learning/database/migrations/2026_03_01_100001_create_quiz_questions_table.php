<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');

            $table->string('type', 30);

            $table->text('content');

            $table->json('options')->nullable();

            $table->json('answer_key')->nullable();

            $table->decimal('weight', 8, 2)->default(1.00);

            $table->integer('order')->default(0);

            $table->decimal('max_score', 8, 2)->default(100.00);

            $table->timestamps();

            $table->index('quiz_id', 'idx_quiz_questions_quiz');
            $table->index('type', 'idx_quiz_questions_type');
            $table->index('order', 'idx_quiz_questions_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
