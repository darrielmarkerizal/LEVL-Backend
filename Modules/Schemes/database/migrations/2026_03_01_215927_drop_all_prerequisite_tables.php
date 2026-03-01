<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('content_prerequisites');
        Schema::dropIfExists('quiz_prerequisites');
        Schema::dropIfExists('assignment_prerequisites');
    }

    public function down(): void
    {
        Schema::create('assignment_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->onDelete('cascade');
            $table->foreignId('prerequisite_id')->constrained('assignments')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['assignment_id', 'prerequisite_id']);
        });

        Schema::create('quiz_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->foreignId('prerequisite_id')->constrained('quizzes')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['quiz_id', 'prerequisite_id']);
        });

        Schema::create('content_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->morphs('content');
            $table->morphs('prerequisite');
            $table->timestamps();
            $table->unique(['content_type', 'content_id', 'prerequisite_type', 'prerequisite_id'], 'uniq_content_prerequisite');
        });
    }
};
