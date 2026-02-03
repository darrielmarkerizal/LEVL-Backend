<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     
    public function up(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            
            
            $table->index(['scheme_id', 'is_pinned', 'last_activity_at'], 'threads_main_sort_index');

            
            $table->index(['scheme_id', 'author_id']);
            
            
            $table->index(['scheme_id', 'is_resolved']);
            $table->index(['scheme_id', 'is_closed']);
        });
    }

     
    public function down(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropIndex('threads_main_sort_index');
            $table->dropIndex(['scheme_id', 'author_id']);
            $table->dropIndex(['scheme_id', 'is_resolved']);
            $table->dropIndex(['scheme_id', 'is_closed']);
        });
    }
};
