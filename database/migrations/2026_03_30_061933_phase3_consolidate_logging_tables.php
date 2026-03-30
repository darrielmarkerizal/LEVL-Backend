<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Consolidate redundant logging tables
     * Keep activity_log (Spatie), drop audit_logs, user_activities, profile_audit_logs
     */
    public function up(): void
    {
        // Migrate profile_audit_logs to activity_log
        if (Schema::hasTable('profile_audit_logs')) {
            DB::statement("
                INSERT INTO activity_log (
                    log_name, description, subject_type, subject_id, 
                    causer_type, causer_id, properties, created_at
                )
                SELECT 
                    'profile_audit' as log_name,
                    CONCAT('Profile ', action) as description,
                    'Modules\\\\Auth\\\\Models\\\\User' as subject_type,
                    user_id as subject_id,
                    'Modules\\\\Auth\\\\Models\\\\User' as causer_type,
                    admin_id as causer_id,
                    json_build_object(
                        'action', action,
                        'changes', changes,
                        'ip_address', ip_address,
                        'user_agent', user_agent
                    ) as properties,
                    created_at
                FROM profile_audit_logs
                WHERE NOT EXISTS (
                    SELECT 1 FROM activity_log a 
                    WHERE a.subject_type = 'Modules\\\\Auth\\\\Models\\\\User'
                    AND a.subject_id = profile_audit_logs.user_id
                    AND a.created_at = profile_audit_logs.created_at
                )
            ");
            
            Schema::dropIfExists('profile_audit_logs');
        }

        // Migrate user_activities to activity_log (if exists)
        if (Schema::hasTable('user_activities')) {
            DB::statement("
                INSERT INTO activity_log (
                    log_name, description, subject_type, subject_id, 
                    causer_type, causer_id, properties, created_at
                )
                SELECT 
                    'user_activity' as log_name,
                    activity_type as description,
                    COALESCE(related_type, 'App\\\\Models\\\\User') as subject_type,
                    COALESCE(related_id, user_id) as subject_id,
                    'Modules\\\\Auth\\\\Models\\\\User' as causer_type,
                    user_id as causer_id,
                    COALESCE(activity_data, '{}'::json) as properties,
                    created_at
                FROM user_activities
                WHERE NOT EXISTS (
                    SELECT 1 FROM activity_log a 
                    WHERE a.causer_id = user_activities.user_id
                    AND a.created_at = user_activities.created_at
                    AND a.description = user_activities.activity_type
                )
            ");
            
            Schema::dropIfExists('user_activities');
        }

        // Drop audit_logs (too similar to activity_log)
        Schema::dropIfExists('audit_logs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate profile_audit_logs
        Schema::create('profile_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->string('field_name');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Recreate user_activities
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('activity_type');
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'activity_type']);
        });

        // Recreate audit_logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event');
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->string('user_type')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();
            $table->text('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();
            
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_type', 'user_id']);
        });
    }
};
