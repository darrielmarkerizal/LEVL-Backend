<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        
        if (Schema::hasTable('lesson_completions')) {
            DB::statement("
                INSERT INTO lesson_progress (enrollment_id, lesson_id, status, progress_percent, completed_at, created_at, updated_at)
                SELECT 
                    e.id as enrollment_id,
                    lc.lesson_id,
                    'completed' as status,
                    100 as progress_percent,
                    lc.completed_at,
                    lc.created_at,
                    lc.updated_at
                FROM lesson_completions lc
                INNER JOIN lessons l ON lc.lesson_id = l.id
                INNER JOIN units u ON l.unit_id = u.id
                INNER JOIN enrollments e ON e.user_id = lc.user_id AND e.course_id = u.course_id
                WHERE NOT EXISTS (
                    SELECT 1 FROM lesson_progress lp 
                    WHERE lp.lesson_id = lc.lesson_id 
                    AND lp.enrollment_id = e.id
                )
                ON CONFLICT (enrollment_id, lesson_id) DO NOTHING
            ");

            
            Schema::dropIfExists('lesson_completions');
        }
    }

    
    public function down(): void
    {
        
        Schema::create('lesson_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['lesson_id', 'user_id']);
        });

        
        DB::statement("
            INSERT INTO lesson_completions (lesson_id, user_id, completed_at, created_at, updated_at)
            SELECT 
                lp.lesson_id,
                e.user_id,
                lp.completed_at,
                lp.created_at,
                lp.updated_at
            FROM lesson_progress lp
            INNER JOIN enrollments e ON lp.enrollment_id = e.id
            WHERE lp.status = 'completed'
            ON CONFLICT (lesson_id, user_id) DO NOTHING
        ");
    }
};
