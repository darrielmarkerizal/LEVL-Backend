<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('trash_bins', function (Blueprint $table) {
            
            $indexExists = function (string $indexName): bool {
                $result = DB::select('SELECT 1 FROM pg_indexes WHERE indexname = ?', [$indexName]);

                return ! empty($result);
            };

            
            if (! $indexExists('trash_bins_deleted_by_deleted_at_index')) {
                $table->index(['deleted_by', 'deleted_at'], 'trash_bins_deleted_by_deleted_at_index');
            }

            
            if (! $indexExists('trash_bins_group_uuid_index')) {
                $table->index('group_uuid', 'trash_bins_group_uuid_index');
            }

            
            if (! $indexExists('trash_bins_expires_at_index')) {
                $table->index('expires_at', 'trash_bins_expires_at_index');
            }

            
            if (! $indexExists('trash_bins_resource_type_deleted_at_index')) {
                $table->index(['resource_type', 'deleted_at'], 'trash_bins_resource_type_deleted_at_index');
            }
        });

        
        $indexExists = DB::select("SELECT 1 FROM pg_indexes WHERE indexname = 'trash_bins_metadata_course_id_index'");
        if (empty($indexExists)) {
            DB::statement("CREATE INDEX trash_bins_metadata_course_id_index ON trash_bins ((metadata->>'course_id'))");
        }
    }

    
    public function down(): void
    {
        Schema::table('trash_bins', function (Blueprint $table) {
            
            $indexExists = function (string $indexName): bool {
                $result = DB::select('SELECT 1 FROM pg_indexes WHERE indexname = ?', [$indexName]);

                return ! empty($result);
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

        
        DB::statement('DROP INDEX IF EXISTS trash_bins_metadata_course_id_index');
    }
};
