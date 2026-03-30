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
        // Add scope column to categories table
        if (! Schema::hasColumn('categories', 'scope')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('scope', 50)->default('course')->after('value');
                $table->index('scope');
            });
        }

        // Migrate data from content_categories to categories
        if (Schema::hasTable('content_categories')) {
            DB::statement("
                INSERT INTO categories (name, value, scope, status, created_at, updated_at)
                SELECT 
                    name,
                    LOWER(REGEXP_REPLACE(name, '[^a-zA-Z0-9]+', '_', 'g')) as value,
                    'news' as scope,
                    'active' as status,
                    created_at,
                    updated_at
                FROM content_categories
                WHERE NOT EXISTS (
                    SELECT 1 FROM categories c 
                    WHERE c.name = content_categories.name 
                    AND c.scope = 'news'
                )
            ");

            // Update news_category pivot to use categories table
            if (Schema::hasTable('news_category')) {
                DB::statement("
                    UPDATE news_category nc
                    SET category_id = (
                        SELECT c.id 
                        FROM categories c 
                        INNER JOIN content_categories cc ON c.name = cc.name 
                        WHERE cc.id = nc.category_id 
                        AND c.scope = 'news'
                        LIMIT 1
                    )
                    WHERE EXISTS (
                        SELECT 1 FROM content_categories cc WHERE cc.id = nc.category_id
                    )
                ");
            }

            // Drop content_categories table (with CASCADE to drop foreign keys)
            DB::statement('DROP TABLE IF EXISTS content_categories CASCADE');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate content_categories table
        Schema::create('content_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Migrate news categories back
        DB::statement("
            INSERT INTO content_categories (name, created_at, updated_at)
            SELECT name, created_at, updated_at
            FROM categories
            WHERE scope = 'news'
        ");

        // Update news_category pivot back
        if (Schema::hasTable('news_category')) {
            DB::statement("
                UPDATE news_category nc
                SET category_id = (
                    SELECT cc.id 
                    FROM content_categories cc 
                    INNER JOIN categories c ON cc.name = c.name 
                    WHERE c.id = nc.category_id 
                    AND c.scope = 'news'
                    LIMIT 1
                )
            ");
        }

        // Remove scope column
        if (Schema::hasColumn('categories', 'scope')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex(['scope']);
                $table->dropColumn('scope');
            });
        }
    }
};
