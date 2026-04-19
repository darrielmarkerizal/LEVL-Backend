<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xp_daily_caps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->integer('total_xp_earned')->default(0);
            $table->integer('global_daily_cap')->default(10000); 
            $table->boolean('cap_reached')->default(false);
            $table->timestamp('cap_reached_at')->nullable();
            $table->json('xp_by_source')->nullable(); 
            $table->timestamps();

            $table->unique(['user_id', 'date'], 'user_daily_cap_unique');
            $table->index('date');
            $table->index('cap_reached');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xp_daily_caps');
    }
};
