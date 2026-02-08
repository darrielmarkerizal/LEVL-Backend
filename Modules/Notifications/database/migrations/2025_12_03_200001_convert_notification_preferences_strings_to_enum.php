<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->validateExistingData();

        Schema::table('notification_preferences', function (Blueprint $table) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("ALTER TABLE notification_preferences DROP CONSTRAINT IF EXISTS notification_preferences_category_check");
                DB::statement("ALTER TABLE notification_preferences ADD CONSTRAINT notification_preferences_category_check CHECK (category::text IN ('system', 'assignment', 'assessment', 'grading', 'gamification', 'news', 'custom', 'course_completed', 'enrollment', 'forum_reply_to_thread', 'forum_reply_to_reply'))");

                DB::statement("ALTER TABLE notification_preferences DROP CONSTRAINT IF EXISTS notification_preferences_channel_check");
                DB::statement("ALTER TABLE notification_preferences ADD CONSTRAINT notification_preferences_channel_check CHECK (channel::text IN ('in_app', 'email', 'push'))");

                DB::statement("ALTER TABLE notification_preferences DROP CONSTRAINT IF EXISTS notification_preferences_frequency_check");
                DB::statement("ALTER TABLE notification_preferences ADD CONSTRAINT notification_preferences_frequency_check CHECK (frequency::text IN ('immediate', 'daily', 'weekly', 'never'))");
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

    public function down(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            $table->string('category', 50)->change();
            $table->string('channel', 50)->change();
            $table->string('frequency', 50)->default('immediate')->change();
        });
    }

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

        $invalidCategory = DB::table('notification_preferences')
            ->whereNotIn('category', $validCategories)
            ->count();

        if ($invalidCategory > 0) {
            throw new \RuntimeException(
                "Found {$invalidCategory} records with invalid category values. Please fix before migration."
            );
        }

        $invalidChannel = DB::table('notification_preferences')
            ->whereNotIn('channel', ['in_app', 'email', 'push'])
            ->count();

        if ($invalidChannel > 0) {
            throw new \RuntimeException(
                "Found {$invalidChannel} records with invalid channel values. Please fix before migration."
            );
        }

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
