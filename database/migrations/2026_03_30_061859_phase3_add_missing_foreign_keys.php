<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        
        if (! Schema::hasTable('news_category')) {
            return;
        }

        
        $contentCategoriesExists = Schema::hasTable('content_categories');
        $categoriesExists = Schema::hasTable('categories');
        
        if (! $categoriesExists) {
            return;
        }

        
        Schema::table('news_category', function (Blueprint $table) {
            
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

    
    public function down(): void
    {
        if (! Schema::hasTable('news_category')) {
            return;
        }

        try {
            Schema::table('news_category', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
            });
        } catch (\Exception $e) {
            
        }
    }
};
