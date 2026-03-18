<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('points', function (Blueprint $table) {
            // Drop enum constraints to allow any source_type and reason
            // We'll use string instead for flexibility
        });

        // Change enum to string for source_type
        DB::statement('ALTER TABLE points ALTER COLUMN source_type TYPE VARCHAR(50)');
        DB::statement('ALTER TABLE points ALTER COLUMN source_type DROP DEFAULT');
        DB::statement("ALTER TABLE points ALTER COLUMN source_type SET DEFAULT 'system'");

        // Change enum to string for reason
        DB::statement('ALTER TABLE points ALTER COLUMN reason TYPE VARCHAR(100)');
        DB::statement('ALTER TABLE points ALTER COLUMN reason DROP DEFAULT');
        DB::statement("ALTER TABLE points ALTER COLUMN reason SET DEFAULT 'completion'");

        Schema::table('points', function (Blueprint $table) {
            // Add transaction metadata
            $table->string('xp_source_code')->nullable()->after('reason');
            $table->integer('old_level')->nullable()->after('xp_source_code');
            $table->integer('new_level')->nullable()->after('old_level');
            $table->boolean('triggered_level_up')->default(false)->after('new_level');
            $table->json('metadata')->nullable()->after('triggered_level_up');
            $table->ipAddress('ip_address')->nullable()->after('metadata');
            $table->string('user_agent')->nullable()->after('ip_address');

            // Add indexes for analytics
            $table->index('xp_source_code');
            $table->index('triggered_level_up');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('points', function (Blueprint $table) {
            $table->dropIndex(['xp_source_code']);
            $table->dropIndex(['triggered_level_up']);
            $table->dropIndex(['created_at']);

            $table->dropColumn([
                'xp_source_code',
                'old_level',
                'new_level',
                'triggered_level_up',
                'metadata',
                'ip_address',
                'user_agent',
            ]);
        });

        // Restore enum types (simplified - may need adjustment based on data)
        DB::statement('ALTER TABLE points ALTER COLUMN source_type TYPE VARCHAR(50)');
        DB::statement('ALTER TABLE points ALTER COLUMN reason TYPE VARCHAR(100)');
    }
};
