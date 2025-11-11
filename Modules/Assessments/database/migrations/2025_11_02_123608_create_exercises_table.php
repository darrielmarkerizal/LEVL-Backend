<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->enum('scope_type', ['course', 'unit', 'lesson']);
            $table->unsignedBigInteger('scope_id');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('type', ['quiz', 'exam'])->default('quiz');
            $table->integer('time_limit_minutes')->nullable();
            $table->integer('max_score')->default(100);
            $table->integer('total_questions')->default(0);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_until')->nullable();
            $table->timestamps();

            $table->index(['scope_type', 'scope_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
