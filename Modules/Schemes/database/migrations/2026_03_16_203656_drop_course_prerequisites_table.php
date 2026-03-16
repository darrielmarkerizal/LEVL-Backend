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
        Schema::dropIfExists('course_prerequisites');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('course_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('prerequisite_course_id')->constrained('courses')->onDelete('cascade');
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->unique(['course_id', 'prerequisite_course_id']);
            $table->index('course_id');
        });
    }
};
