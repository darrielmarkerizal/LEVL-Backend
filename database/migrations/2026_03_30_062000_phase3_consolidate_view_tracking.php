<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        if (Schema::hasTable('post_views')) {
            
            DB::statement("
                INSERT INTO content_reads (
                    readable_type, readable_id, user_id, read_at
                )
                SELECT 
                    'Modules\\\\Notifications\\\\Models\\\\Post' as readable_type,
                    post_id as readable_id,
                    user_id,
                    viewed_at as read_at
                FROM post_views
                WHERE NOT EXISTS (
                    SELECT 1 FROM content_reads cr 
                    WHERE cr.readable_type = 'Modules\\\\Notifications\\\\Models\\\\Post'
                    AND cr.readable_id = post_views.post_id
                    AND cr.user_id = post_views.user_id
                )
            ");
            
            Schema::dropIfExists('post_views');
        }
    }

    
    public function down(): void
    {
        
        Schema::create('post_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('viewed_at')->useCurrent();
            
            $table->unique(['post_id', 'user_id']);
        });

        
        DB::statement("
            INSERT INTO post_views (post_id, user_id, viewed_at)
            SELECT 
                readable_id as post_id,
                user_id,
                read_at as viewed_at
            FROM content_reads
            WHERE readable_type = 'Modules\\\\Notifications\\\\Models\\\\Post'
            ON CONFLICT (post_id, user_id) DO NOTHING
        ");
    }
};
