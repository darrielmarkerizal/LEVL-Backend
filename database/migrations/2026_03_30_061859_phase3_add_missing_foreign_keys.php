<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing foreign key constraints
     */
    public function up(): void
    {
        // Add FK for news_category.category_id -> categories.id
        Schema::table('news_category', function (Blueprint $table) {
            // Check if FK already exists using raw SQL
            $exists = DB::selectOne("
                SELECT COUNT(*) as count
                FROM information_schema.table_constraints 
                WHERE constraint_type = 'FOREIGN KEY'
                AND table_name = 'news_category'
                AND constraint_name = 'news_category_category_id_foreign'
            ");
            
            if ($exists->count == 0) {
                $table->foreign('category_id')
                    ->references('id')
                    ->on('categories')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news_category', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });
    }
};
