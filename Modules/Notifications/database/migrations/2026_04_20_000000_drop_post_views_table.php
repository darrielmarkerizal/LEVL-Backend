<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('post_views');
    }

    public function down(): void
    {
        Schema::create('post_views', function ($table): void {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('viewed_at')->useCurrent();
            $table->unique(['post_id', 'user_id']);
            $table->index('user_id');
        });
    }
};
