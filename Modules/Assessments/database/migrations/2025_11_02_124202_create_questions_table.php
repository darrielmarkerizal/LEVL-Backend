<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_id')->constrained('exercises')->onDelete('cascade');
            $table->text('question_text');
            $table->enum('type', ['multiple_choice', 'free_text', 'file_upload'])->default('multiple_choice');
            $table->integer('score_weight')->default(1);
            $table->boolean('is_required')->default(true);
            $table->integer('order')->default(1);
            $table->timestamps();

            $table->index(['exercise_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
