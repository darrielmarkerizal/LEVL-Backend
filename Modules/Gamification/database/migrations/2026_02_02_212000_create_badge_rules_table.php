<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('badge_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('badge_id')->constrained('badges')->cascadeOnDelete();
            $table->string('criterion'); // e.g., 'course_completed_count', 'lesson_completed_count'
            $table->string('operator')->default('>='); // e.g., '>=', '=', '>'
            $table->integer('value'); // e.g., 5, 10
            $table->timestamps();

            // Index for faster lookup by criterion
            $table->index('criterion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badge_rules');
    }
};
