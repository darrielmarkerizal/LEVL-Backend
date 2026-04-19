<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            
            $table->index('category_id', 'idx_courses_category');

            
            $table->index('instructor_id', 'idx_courses_instructor');

            
            $table->index(['category_id', 'status'], 'idx_courses_category_status');

            
            
        });

        Schema::table('units', function (Blueprint $table) {
            
            $table->index(['course_id', 'order'], 'idx_units_course_order');
        });
    }

    
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex('idx_courses_category');
            $table->dropIndex('idx_courses_instructor');
            $table->dropIndex('idx_courses_category_status');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropIndex('idx_units_course_order');
        });
    }
};
