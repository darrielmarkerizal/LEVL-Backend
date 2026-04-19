<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        
        
        Schema::table('courses', function (Blueprint $table) {
            $table->index(['status', 'deleted_at', 'published_at'], 'idx_courses_listing');
        });

        
        
        Schema::table('units', function (Blueprint $table) {
            $table->index(['course_id'], 'idx_units_course_id');
        });

        
        
        Schema::table('enrollments', function (Blueprint $table) {
            $table->index(['user_id', 'course_id', 'status'], 'idx_enrollments_user_course_status');
        });
    }

    
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex('idx_courses_listing');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropIndex('idx_units_course_id');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex('idx_enrollments_user_course_status');
        });
    }
};
