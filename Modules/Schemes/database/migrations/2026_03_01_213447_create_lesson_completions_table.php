<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->unique(['lesson_id', 'user_id'], 'uniq_lesson_user_completion');
            $table->index('user_id', 'idx_completion_user');
            $table->index('lesson_id', 'idx_completion_lesson');
            $table->index('completed_at', 'idx_completion_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_completions');
    }
};
