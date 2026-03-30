<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add CHECK constraints to polymorphic relations for data integrity
     */
    public function up(): void
    {
        // Add CHECK constraint for grades.source_type
        DB::statement("
            ALTER TABLE grades 
            ADD CONSTRAINT grades_source_type_valid 
            CHECK (source_type IN ('assignment', 'attempt'))
        ");

        // Add CHECK constraint for trash_bins.trashable_type
        $trashableTypes = [
            'Modules\\\\Schemes\\\\Models\\\\Course',
            'Modules\\\\Schemes\\\\Models\\\\Unit',
            'Modules\\\\Schemes\\\\Models\\\\Lesson',
            'Modules\\\\Learning\\\\Models\\\\Assignment',
            'Modules\\\\Learning\\\\Models\\\\Quiz',
            'Modules\\\\Notifications\\\\Models\\\\Post',
            'Modules\\\\Notifications\\\\Models\\\\Announcement',
            'Modules\\\\Notifications\\\\Models\\\\News',
        ];
        
        $typesList = implode("', '", $trashableTypes);
        DB::statement("
            ALTER TABLE trash_bins 
            ADD CONSTRAINT trash_bins_trashable_type_valid 
            CHECK (trashable_type IN ('{$typesList}'))
        ");

        // Add CHECK constraint for taggables.taggable_type
        DB::statement("
            ALTER TABLE taggables 
            ADD CONSTRAINT taggables_taggable_type_valid 
            CHECK (taggable_type IN ('Modules\\\\Schemes\\\\Models\\\\Course'))
        ");

        // Add CHECK constraint for content_reads.readable_type
        DB::statement("
            ALTER TABLE content_reads 
            ADD CONSTRAINT content_reads_readable_type_valid 
            CHECK (readable_type IN (
                'Modules\\\\Notifications\\\\Models\\\\Post',
                'Modules\\\\Notifications\\\\Models\\\\Announcement',
                'Modules\\\\Notifications\\\\Models\\\\News'
            ))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE grades DROP CONSTRAINT IF EXISTS grades_source_type_valid');
        DB::statement('ALTER TABLE trash_bins DROP CONSTRAINT IF EXISTS trash_bins_trashable_type_valid');
        DB::statement('ALTER TABLE taggables DROP CONSTRAINT IF EXISTS taggables_taggable_type_valid');
        DB::statement('ALTER TABLE content_reads DROP CONSTRAINT IF EXISTS content_reads_readable_type_valid');
    }
};
