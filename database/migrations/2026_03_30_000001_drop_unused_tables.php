<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop tables that are proven unused in the codebase.
     *
     * - audits: SystemAudit model exists but is never called from any service/controller
     * - social_accounts: No social login implementation exists
     * - login_activities: Login data already tracked via activity_log (Spatie)
     * - notification_templates: Not used by any service
     * - reports: No active controller/service references
     * - submission_files: Empty shell, files managed via Spatie Media Library
     * - levels: Redundant with user_gamification_stats + user_scope_stats
     * - telescope_*: Development-only debugging tables
     */
    public function up(): void
    {
        // Drop foreign key constraints first where needed
        Schema::dropIfExists('submission_files');
        Schema::dropIfExists('levels');
        Schema::dropIfExists('social_accounts');
        Schema::dropIfExists('login_activities');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('audits');
        Schema::dropIfExists('telescope_entries_tags');
        Schema::dropIfExists('telescope_entries');
        Schema::dropIfExists('telescope_monitoring');
    }

    public function down(): void
    {
        // Recreate audits table
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->string('action')->default('system');
            $table->string('actor_type')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('target_table', 100)->nullable();
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('module', 100)->nullable();
            $table->string('context')->default('application');
            $table->string('ip_address', 50)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('meta')->nullable();
            $table->json('properties')->nullable();
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // Recreate social_accounts table
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider_name', 50);
            $table->string('provider_id', 191);
            $table->text('token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamps();
            $table->unique(['provider_name', 'provider_id']);
        });

        // Recreate login_activities table
        Schema::create('login_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('status');
            $table->timestamp('logged_in_at')->nullable();
            $table->timestamp('logged_out_at')->nullable();
            $table->timestamps();
        });

        // Recreate notification_templates table
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->string('title');
            $table->text('body');
            $table->string('channel')->default('in_app');
            $table->timestamps();
        });

        // Recreate reports table
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('activity');
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->json('filters')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();
        });

        // Recreate submission_files table
        Schema::create('submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Recreate levels table
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('course_id')->nullable();
            $table->integer('current_level')->default(1);
            $table->timestamps();
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });

        // Recreate telescope tables
        Schema::create('telescope_entries', function (Blueprint $table) {
            $table->bigIncrements('sequence');
            $table->uuid('uuid');
            $table->uuid('batch_id');
            $table->string('family_hash')->nullable();
            $table->boolean('should_display_on_index')->default(true);
            $table->string('type', 20);
            $table->longText('content');
            $table->dateTime('created_at')->nullable();
            $table->unique('uuid');
            $table->index('batch_id');
            $table->index('family_hash');
            $table->index('created_at');
            $table->index(['type', 'should_display_on_index']);
        });

        Schema::create('telescope_entries_tags', function (Blueprint $table) {
            $table->uuid('entry_uuid');
            $table->string('tag');
            $table->index(['entry_uuid', 'tag']);
            $table->index('tag');
            $table->foreign('entry_uuid')
                ->references('uuid')
                ->on('telescope_entries')
                ->onDelete('cascade');
        });

        Schema::create('telescope_monitoring', function (Blueprint $table) {
            $table->string('tag')->primary();
        });
    }
};
