<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('content_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->morphs('readable');
            $table->timestamp('read_at')->useCurrent();

            
            $table->unique(['user_id', 'readable_type', 'readable_id'], 'unique_user_read');
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('content_reads');
    }
};
