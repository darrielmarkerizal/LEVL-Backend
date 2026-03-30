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
        Schema::table('audit_logs', function (Blueprint $table) {
            // Drop redundant columns - keep subject_type/subject_id, drop target_type/target_id
            // Keep actor_type/actor_id, drop user_id (can be derived from actor)
            
            // First, migrate any data from target_* to subject_* if subject is null
            DB::statement("
                UPDATE audit_logs 
                SET subject_type = target_type, subject_id = target_id 
                WHERE subject_type IS NULL AND target_type IS NOT NULL
            ");

            // Drop the redundant columns
            $table->dropColumn(['target_type', 'target_id', 'event']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Recreate the dropped columns
            $table->string('target_type')->nullable()->after('actor_id');
            $table->unsignedBigInteger('target_id')->nullable()->after('target_type');
            $table->string('event')->nullable()->after('id');
        });

        // Migrate data back
        DB::statement("
            UPDATE audit_logs 
            SET target_type = subject_type, target_id = subject_id 
            WHERE subject_type IS NOT NULL
        ");
    }
};
