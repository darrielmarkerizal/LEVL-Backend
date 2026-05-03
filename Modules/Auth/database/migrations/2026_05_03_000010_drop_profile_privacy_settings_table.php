<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('profile_privacy_settings');
    }

    public function down(): void
    {
        Schema::create('profile_privacy_settings', function ($table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('profile_visibility')->default('public');
            $table->boolean('show_email')->default(false);
            $table->boolean('show_phone')->default(false);
            $table->boolean('show_activity_history')->default(true);
            $table->boolean('show_achievements')->default(true);
            $table->boolean('show_statistics')->default(true);
            $table->timestamps();
        });
    }
};
