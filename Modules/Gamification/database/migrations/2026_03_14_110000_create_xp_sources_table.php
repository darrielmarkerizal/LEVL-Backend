<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xp_sources', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // lesson_completed, assignment_submitted, etc
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('xp_amount')->default(0);
            $table->integer('cooldown_seconds')->default(0); // Cooldown between same actions
            $table->integer('daily_limit')->nullable(); // Max times per day (null = unlimited)
            $table->integer('daily_xp_cap')->nullable(); // Max XP per day from this source
            $table->boolean('allow_multiple')->default(true); // Can earn multiple times from same source_id
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // Additional config
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xp_sources');
    }
};
