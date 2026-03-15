<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('content');
            $table->enum('category', [
                'announcement',
                'information',
                'gamification',
                'warning',
                'system',
                'award',
            ]);
            $table->enum('status', ['draft', 'scheduled', 'published'])->default('draft');

            $table->boolean('is_pinned')->default(false);
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('last_editor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // FK indexes (PostgreSQL does NOT auto-index foreign keys)
            $table->index('author_id');
            $table->index('last_editor_id');

            // Composite indexes for common query patterns
            $table->index(['status', 'published_at']);
            $table->index(['status', 'category']);

            // Soft delete index
            $table->index('deleted_at');
        });

        // Partial indexes (PostgreSQL only) — smaller and faster than full indexes
        DB::statement("CREATE INDEX idx_posts_published ON posts (published_at) WHERE status = 'published'");
        DB::statement("CREATE INDEX idx_posts_scheduled ON posts (scheduled_at) WHERE status = 'scheduled'");
        DB::statement("CREATE INDEX idx_posts_pinned ON posts (created_at) WHERE is_pinned = true");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_posts_published');
        DB::statement('DROP INDEX IF EXISTS idx_posts_scheduled');
        DB::statement('DROP INDEX IF EXISTS idx_posts_pinned');

        Schema::dropIfExists('posts');
    }
};