<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public $withinTransaction = false;

    
    public function up(): void
    {
        Schema::create('master_data', function (Blueprint $table) {
            $table->id();

            $table->string('type', 50);
            $table->string('value', 100);
            $table->string('label', 255);

            $table->json('metadata')->nullable();

            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);

            
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            
            $table->unique(['type', 'value'], 'md_type_value_unique');

            
            $table->index(['type', 'is_active'], 'md_type_active_index');
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('master_data');
    }
};