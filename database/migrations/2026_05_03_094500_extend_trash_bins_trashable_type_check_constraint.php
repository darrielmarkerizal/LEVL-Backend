<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE trash_bins DROP CONSTRAINT IF EXISTS trash_bins_trashable_type_valid');

        $trashableTypes = [
            'Modules\\Schemes\\Models\\Course',
            'Modules\\Schemes\\Models\\Unit',
            'Modules\\Schemes\\Models\\Lesson',
            'Modules\\Learning\\Models\\Assignment',
            'Modules\\Learning\\Models\\Quiz',
            'Modules\\Notifications\\Models\\Post',
            'Modules\\Notifications\\Models\\Announcement',
            'Modules\\Notifications\\Models\\News',
            'Modules\\Auth\\Models\\User',
            'Modules\\Gamification\\Models\\Badge',
            'Modules\\Content\\Models\\News',
        ];

        $typesList = implode("', '", $trashableTypes);

        DB::statement("
            ALTER TABLE trash_bins
            ADD CONSTRAINT trash_bins_trashable_type_valid
            CHECK (trashable_type IN ('{$typesList}'))
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE trash_bins DROP CONSTRAINT IF EXISTS trash_bins_trashable_type_valid');

        $trashableTypes = [
            'Modules\\Schemes\\Models\\Course',
            'Modules\\Schemes\\Models\\Unit',
            'Modules\\Schemes\\Models\\Lesson',
            'Modules\\Learning\\Models\\Assignment',
            'Modules\\Learning\\Models\\Quiz',
            'Modules\\Notifications\\Models\\Post',
            'Modules\\Notifications\\Models\\Announcement',
            'Modules\\Notifications\\Models\\News',
        ];

        $typesList = implode("', '", $trashableTypes);

        DB::statement("
            ALTER TABLE trash_bins
            ADD CONSTRAINT trash_bins_trashable_type_valid
            CHECK (trashable_type IN ('{$typesList}'))
        ");
    }
};
