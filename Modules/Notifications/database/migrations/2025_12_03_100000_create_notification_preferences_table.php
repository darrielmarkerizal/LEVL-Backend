<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('category', 50);
            $table->string('channel', 50);
            $table->boolean('enabled')->default(true);
            $table->string('frequency', 50)->default('immediate');
            $table->timestamps();

            
            $table->unique(['user_id', 'category', 'channel'], 'unique_user_category_channel');

            
            $table->index('user_id');
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
