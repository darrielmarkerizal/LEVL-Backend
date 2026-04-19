<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        
        if (Schema::hasTable('course_tag_pivot')) {
            DB::statement("
                INSERT INTO taggables (tag_id, taggable_type, taggable_id, created_at, updated_at)
                SELECT 
                    tag_id,
                    'Modules\\\\Schemes\\\\Models\\\\Course' as taggable_type,
                    course_id as taggable_id,
                    created_at,
                    updated_at
                FROM course_tag_pivot
                WHERE NOT EXISTS (
                    SELECT 1 FROM taggables t 
                    WHERE t.tag_id = course_tag_pivot.tag_id 
                    AND t.taggable_type = 'Modules\\\\Schemes\\\\Models\\\\Course'
                    AND t.taggable_id = course_tag_pivot.course_id
                )
            ");

            
            Schema::dropIfExists('course_tag_pivot');
        }
    }

    
    public function down(): void
    {
        
        Schema::create('course_tag_pivot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['course_id', 'tag_id']);
        });

        
        DB::statement("
            INSERT INTO course_tag_pivot (course_id, tag_id, created_at, updated_at)
            SELECT 
                taggable_id as course_id,
                tag_id,
                created_at,
                updated_at
            FROM taggables
            WHERE taggable_type = 'Modules\\\\Schemes\\\\Models\\\\Course'
            ON CONFLICT (course_id, tag_id) DO NOTHING
        ");
    }
};
