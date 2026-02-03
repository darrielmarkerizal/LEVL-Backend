<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     
    public function up(): void
    {
        if (! Schema::hasTable('reactions')) {
            Schema::create('reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->morphs('reactable');
                $table->enum('type', ['like', 'helpful', 'solved']);
                $table->timestamp('created_at')->useCurrent();

                
                $table->unique(['user_id', 'reactable_type', 'reactable_id', 'type'], 'unique_user_reaction');
            });
        }
    }

     
    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
