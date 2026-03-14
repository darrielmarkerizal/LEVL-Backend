<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_badges', function (Blueprint $table) {
            // Drop old unique constraint
            $table->dropUnique(['user_id', 'badge_id']);
            
            // Add badge_version_id if not exists (should be added by badge_versions migration)
            if (!Schema::hasColumn('user_badges', 'badge_version_id')) {
                $table->foreignId('badge_version_id')->nullable()
                    ->after('badge_id')
                    ->constrained('badge_versions')
                    ->nullOnDelete();
            }
            
            // Add new unique constraint with version
            // This prevents duplicate badge awards for same version
            $table->unique(['user_id', 'badge_id', 'badge_version_id'], 'user_badge_version_unique');
        });
    }

    public function down(): void
    {
        Schema::table('user_badges', function (Blueprint $table) {
            $table->dropUnique('user_badge_version_unique');
            $table->unique(['user_id', 'badge_id']);
        });
    }
};
