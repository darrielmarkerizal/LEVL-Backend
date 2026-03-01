<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->foreignId('prerequisite_id')->constrained('quizzes')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['quiz_id', 'prerequisite_id'], 'uniq_quiz_prerequisite');
            $table->index('quiz_id', 'idx_quiz_prereq_quiz');
            $table->index('prerequisite_id', 'idx_quiz_prereq_prerequisite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_prerequisites');
    }
};
