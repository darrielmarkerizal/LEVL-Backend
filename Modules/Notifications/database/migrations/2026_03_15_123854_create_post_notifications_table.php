<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->enum('channel', ['email', 'in_app', 'push']);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['post_id', 'channel']);
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_notifications');
    }
};