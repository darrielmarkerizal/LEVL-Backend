<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trash_bins', function (Blueprint $table) {
            // Check and add indexes only if they don't exist
            $indexExists = function (string $indexName): bool {
                $result = DB::select("SELECT 1 FROM pg_indexes WHERE indexname = ?", [$indexName]);
                return !empty($result);
            };
            
            // Add composite index for deleted_by and deleted_at (common filter combination)
            if (!$indexExists('trash_bins_deleted_by_deleted_at_index')) {
                $table->index(['deleted_by', 'deleted_at'], 'trash_bins_deleted_by_deleted_at_index');
            }
            
            // Add index for group_uuid (used for cascade operations)
            if (!$indexExists('trash_bins_group_uuid_index')) {
                $table->index('group_uuid', 'trash_bins_group_uuid_index');
            }
            
            // Add index for expires_at (used in purge operations)
            if (!$indexExists('trash_bins_expires_at_index')) {
                $table->index('expires_at', 'trash_bins_expires_at_index');
            }
            
            // Add composite index for resource_type and deleted_at (common filter)
            if (!$indexExists('trash_bins_resource_type_deleted_at_index')) {
                $table->index(['resource_type', 'deleted_at'], 'trash_bins_resource_type_deleted_at_index');
            }
        });
        
        // Add index for JSON field metadata->>'course_id' (PostgreSQL specific)
        $indexExists = DB::select("SELECT 1 FROM pg_indexes WHERE indexname = 'trash_bins_metadata_course_id_index'");
        if (empty($indexExists)) {
            DB::statement("CREATE INDEX trash_bins_metadata_course_id_index ON trash_bins ((metadata->>'course_id'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trash_bins', function (Blueprint $table) {
            // Drop indexes in reverse order (check if exists first)
            $indexExists = function (string $indexName): bool {
                $result = DB::select("SELECT 1 FROM pg_indexes WHERE indexname = ?", [$indexName]);
                return !empty($result);
            };
            
            if ($indexExists('trash_bins_deleted_by_deleted_at_index')) {
                $table->dropIndex('trash_bins_deleted_by_deleted_at_index');
            }
            if ($indexExists('trash_bins_group_uuid_index')) {
                $table->dropIndex('trash_bins_group_uuid_index');
            }
            if ($indexExists('trash_bins_expires_at_index')) {
                $table->dropIndex('trash_bins_expires_at_index');
            }
            if ($indexExists('trash_bins_resource_type_deleted_at_index')) {
                $table->dropIndex('trash_bins_resource_type_deleted_at_index');
            }
        });
        
        // Drop JSON index separately
        DB::statement("DROP INDEX IF EXISTS trash_bins_metadata_course_id_index");
    }
};
