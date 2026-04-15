<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old news_category pivot table if it exists
        Schema::dropIfExists('news_category');

        // Recreate news_category pivot table with correct foreign key to categories
        Schema::create('news_category', function (Blueprint $table) {
            $table->foreignId('news_id')->constrained('news')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');

            $table->primary(['news_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_category');

        // Recreate with old structure (pointing to content_categories)
        Schema::create('news_category', function (Blueprint $table) {
            $table->foreignId('news_id')->constrained('news')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('content_categories')->onDelete('cascade');

            $table->primary(['news_id', 'category_id']);
        });
    }
};
