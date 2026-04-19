<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        
        Schema::dropIfExists('news_category');

        
        Schema::create('news_category', function (Blueprint $table) {
            $table->foreignId('news_id')->constrained('news')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');

            $table->primary(['news_id', 'category_id']);
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('news_category');

        
        Schema::create('news_category', function (Blueprint $table) {
            $table->foreignId('news_id')->constrained('news')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('content_categories')->onDelete('cascade');

            $table->primary(['news_id', 'category_id']);
        });
    }
};
