<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('badge_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('badge_id')->constrained('badges')->cascadeOnDelete();
            $table->string('criterion'); 
            $table->string('operator')->default('>='); 
            $table->integer('value'); 
            $table->timestamps();

            
            $table->index('criterion');
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('badge_rules');
    }
};
