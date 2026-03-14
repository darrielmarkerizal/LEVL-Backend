<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badge_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('badge_id')->constrained('badges')->cascadeOnDelete();
            $table->integer('version')->default(1);
            $table->integer('threshold');
            $table->json('rules'); // snapshot of rules at this version
            $table->timestamp('effective_from');
            $table->timestamp('effective_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['badge_id', 'version'], 'badge_version_unique');
            $table->index(['badge_id', 'is_active'], 'badge_active_idx');
        });

        // Note: user_badge_progress table will be created in future upgrade
        // This is part of Phase 1 of DUOLINGO_LEVEL_UPGRADE_PLAN.md
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_versions');
    }
};
