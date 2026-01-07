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
        // Add workflow statuses to news table
        Schema::table('news', function (Blueprint $table) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("ALTER TABLE news DROP CONSTRAINT IF EXISTS news_status_check");
                DB::statement("ALTER TABLE news ADD CONSTRAINT news_status_check CHECK (status::text IN ('draft', 'submitted', 'in_review', 'approved', 'rejected', 'scheduled', 'published', 'archived'))");
            } else {
                $table->enum('status', ['draft', 'submitted', 'in_review', 'approved', 'rejected', 'scheduled', 'published', 'archived'])->default('draft')->change();
            }
        });

        // Add workflow statuses to announcements table
        Schema::table('announcements', function (Blueprint $table) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("ALTER TABLE announcements DROP CONSTRAINT IF EXISTS announcements_status_check");
                DB::statement("ALTER TABLE announcements ADD CONSTRAINT announcements_status_check CHECK (status::text IN ('draft', 'submitted', 'in_review', 'approved', 'rejected', 'scheduled', 'published', 'archived'))");
            } else {
                $table->enum('status', ['draft', 'submitted', 'in_review', 'approved', 'rejected', 'scheduled', 'published', 'archived'])->default('draft')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original statuses
        Schema::table('news', function (Blueprint $table) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("ALTER TABLE news DROP CONSTRAINT IF EXISTS news_status_check");
                DB::statement("ALTER TABLE news ADD CONSTRAINT news_status_check CHECK (status::text IN ('draft', 'published', 'scheduled'))");
            } else {
                $table->enum('status', ['draft', 'published', 'scheduled'])->default('draft')->change();
            }
        });

        Schema::table('announcements', function (Blueprint $table) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("ALTER TABLE announcements DROP CONSTRAINT IF EXISTS announcements_status_check");
                DB::statement("ALTER TABLE announcements ADD CONSTRAINT announcements_status_check CHECK (status::text IN ('draft', 'published', 'scheduled'))");
            } else {
                $table->enum('status', ['draft', 'published', 'scheduled'])->default('draft')->change();
            }
        });
    }
};
