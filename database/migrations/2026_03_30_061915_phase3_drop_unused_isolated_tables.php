<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        
        Schema::dropIfExists('grading_rubric_criteria');
        
        
        Schema::dropIfExists('grading_rubrics');
    }

    
    public function down(): void
    {
        
        Schema::create('grading_rubrics', function (Blueprint $table) {
            $table->id();
            $table->string('scope_type'); 
            $table->unsignedBigInteger('scope_id'); 
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('max_score', 5, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['scope_type', 'scope_id']);
        });

        
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
