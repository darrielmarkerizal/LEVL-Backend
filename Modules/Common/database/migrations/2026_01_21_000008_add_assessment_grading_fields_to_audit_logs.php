<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    
    public function up(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->enum('event', [
                    'create', 'update', 'delete', 'login', 'logout', 'assign', 'revoke', 'export', 'import', 'system',
                ])->default('system');
                $table->string('target_type')->nullable();
                $table->unsignedBigInteger('target_id')->nullable();
                $table->string('actor_type')->nullable();
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->json('properties')->nullable();
                $table->timestamp('logged_at')->useCurrent();
                $table->timestamps();

                $table->index(['event', 'logged_at']);
                $table->index(['target_type', 'target_id']);
            });
        }

        Schema::table('audit_logs', function (Blueprint $table) {
            
            $table->string('action')->nullable()->after('event');

            
            $table->string('subject_type')->nullable()->after('actor_id');
            $table->unsignedBigInteger('subject_id')->nullable()->after('subject_type');

            
            $table->json('context')->nullable()->after('properties');

            
            $table->index(['actor_id', 'actor_type'], 'idx_audit_logs_actor');
            $table->index(['subject_id', 'subject_type'], 'idx_audit_logs_subject');
            $table->index('action', 'idx_audit_logs_action');
            $table->index('created_at', 'idx_audit_logs_created_at');
        });
    }

    
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            
            $table->dropIndex('idx_audit_logs_actor');
            $table->dropIndex('idx_audit_logs_subject');
            $table->dropIndex('idx_audit_logs_action');
            $table->dropIndex('idx_audit_logs_created_at');

            
            $table->dropColumn(['action', 'subject_type', 'subject_id', 'context']);
        });
    }
};
