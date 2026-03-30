<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Consolidate view tracking: migrate post_views to content_reads
     */
    public function up(): void
    {
        if (Schema::hasTable('post_views')) {
            // Migrate post_views to content_reads (polymorphic)
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate post_views
        Schema::create('post_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['post_id', 'user_id']);
        });

        // Migrate back from content_reads
        DB::statement("
            INSERT INTO post_views (post_id, user_id, created_at, updated_at)
            SELECT 
                readable_id as post_id,
                user_id,
                created_at,
                updated_at
            FROM content_reads
            WHERE readable_type = 'Modules\\\\Notifications\\\\Models\\\\Post'
            ON CONFLICT (post_id, user_id) DO NOTHING
        ");
    }
};
