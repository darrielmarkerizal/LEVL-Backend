<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Converts string columns to enum types for better data integrity.
     * Requirements: 1.1, 1.5
     */
    public function up(): void
    {
        // Validate existing data before migration
        $this->validateExistingData();

        // Convert category column to enum
        Schema::table('notification_preferences', function (Blueprint $table) {
            if (DB::getDriverName() === 'pgsql') {
                 // For PostgreSQL, changing string to enum logic involves adding check constraints. 
                 // Since they were strings, there likely isn't an existing check constraint to drop, unless this migration ran before.
                 // But this migration keeps them as TYPE VARCHAR (string) in PG basically, just adds constraint to mimic enum.
                 // OR we can explicitly cast them. Laravel schema builder `enum` on PG creates a check constraint on a text column usually.
                 
                 // Category
                 DB::statement("ALTER TABLE notification_preferences DROP CONSTRAINT IF EXISTS notification_preferences_category_check");
                 DB::statement("ALTER TABLE notification_preferences ADD CONSTRAINT notification_preferences_category_check CHECK (category::text IN ('system', 'assignment', 'assessment', 'grading', 'gamification', 'news', 'custom', 'course_completed', 'enrollment', 'forum_reply_to_thread', 'forum_reply_to_reply'))");

                 // Channel
                 DB::statement("ALTER TABLE notification_preferences DROP CONSTRAINT IF EXISTS notification_preferences_channel_check");
                 DB::statement("ALTER TABLE notification_preferences ADD CONSTRAINT notification_preferences_channel_check CHECK (channel::text IN ('in_app', 'email', 'push'))");

                 // Frequency
                 DB::statement("ALTER TABLE notification_preferences DROP CONSTRAINT IF EXISTS notification_preferences_frequency_check");
                 DB::statement("ALTER TABLE notification_preferences ADD CONSTRAINT notification_preferences_frequency_check CHECK (frequency::text IN ('immediate', 'daily', 'weekly', 'never'))");
                 
                 // Set defaults if needed, though they were string before so defaults might be preserved or lost depending on original schema.
                 // The base migration sets defaults. Here we are changing type.
                 // If previous type was string, we don't need to change type in PG as enum is implemented as text+check often, OR as a custom TYPE.
                 // Laravel defaults to check constraint.
            } else {
                $table->enum('category', [
                    'system', 'assignment', 'assessment', 'grading', 'gamification',
                    'news', 'custom', 'course_completed', 'enrollment',
                    'forum_reply_to_thread', 'forum_reply_to_reply'
                ])->change();

                $table->enum('channel', ['in_app', 'email', 'push'])->change();

                $table->enum('frequency', ['immediate', 'daily', 'weekly', 'never'])
                    ->default('immediate')
                    ->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            // Revert category column to string
            $table->string('category', 50)->change();

            // Revert channel column to string
            $table->string('channel', 50)->change();

            // Revert frequency column to string
            $table->string('frequency', 50)->default('immediate')->change();
        });
    }

    /**
     * Validate existing data matches expected enum values.
     *
     * @throws \RuntimeException if invalid data is found
     */
    private function validateExistingData(): void
    {
        if (! Schema::hasTable('notification_preferences')) {
            return;
        }

        $validCategories = [
            'system', 'assignment', 'assessment', 'grading', 'gamification',
            'news', 'custom', 'course_completed', 'enrollment',
            'forum_reply_to_thread', 'forum_reply_to_reply',
        ];

        // Check for invalid category values
        $invalidCategory = DB::table('notification_preferences')
            ->whereNotIn('category', $validCategories)
            ->count();

        if ($invalidCategory > 0) {
            throw new \RuntimeException(
                "Found {$invalidCategory} records with invalid category values. Please fix before migration."
            );
        }

        // Check for invalid channel values
        $invalidChannel = DB::table('notification_preferences')
            ->whereNotIn('channel', ['in_app', 'email', 'push'])
            ->count();

        if ($invalidChannel > 0) {
            throw new \RuntimeException(
                "Found {$invalidChannel} records with invalid channel values. Please fix before migration."
            );
        }

        // Check for invalid frequency values
        $invalidFrequency = DB::table('notification_preferences')
            ->whereNotIn('frequency', ['immediate', 'daily', 'weekly', 'never'])
            ->count();

        if ($invalidFrequency > 0) {
            throw new \RuntimeException(
                "Found {$invalidFrequency} records with invalid frequency values. Please fix before migration."
            );
        }
    }
};
