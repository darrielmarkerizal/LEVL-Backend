<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->foreignId('prerequisite_id')->constrained('lessons')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['lesson_id', 'prerequisite_id'], 'uniq_lesson_prerequisite');
            $table->index('lesson_id', 'idx_lesson_prereq_lesson');
            $table->index('prerequisite_id', 'idx_lesson_prereq_prerequisite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_prerequisites');
    }
};
