<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('post_views', function (Blueprint $table) {
            
            $table->index('viewed_at', 'idx_post_views_viewed_at');
        });
    }

    
    public function down(): void
    {
        Schema::table('post_views', function (Blueprint $table) {
            $table->dropIndex('idx_post_views_viewed_at');
        });
    }
};
