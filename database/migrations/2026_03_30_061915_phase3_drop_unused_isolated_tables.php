<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop unused and isolated tables
     */
    public function up(): void
    {
        // Drop grading_rubric_criteria first (child table)
        Schema::dropIfExists('grading_rubric_criteria');
        
        // Then drop grading_rubrics (parent table)
        Schema::dropIfExists('grading_rubrics');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate grading_rubrics
        Schema::create('grading_rubrics', function (Blueprint $table) {
            $table->id();
            $table->string('scope_type'); // polymorphic
            $table->unsignedBigInteger('scope_id'); // polymorphic
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('max_score', 5, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['scope_type', 'scope_id']);
        });

        // Recreate grading_rubric_criteria
        Schema::create('grading_rubric_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubric_id')->constrained('grading_rubrics')->onDelete('cascade');
            $table->string('criterion_name');
            $table->text('description')->nullable();
            $table->decimal('max_points', 5, 2);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }
};
