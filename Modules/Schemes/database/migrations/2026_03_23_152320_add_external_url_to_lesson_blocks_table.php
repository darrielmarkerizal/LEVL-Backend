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
        Schema::table('lesson_blocks', function (Blueprint $table) {
            $table->string('external_url', 500)->nullable()->after('content');
            
            // Update block_type enum to include new types
            $table->dropColumn('block_type');
        });
        
        Schema::table('lesson_blocks', function (Blueprint $table) {
            $table->enum('block_type', ['text', 'image', 'video', 'file', 'link', 'youtube', 'drive', 'embed'])
                ->default('text')
                ->after('lesson_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_blocks', function (Blueprint $table) {
            $table->dropColumn('external_url');
            $table->dropColumn('block_type');
        });
        
        Schema::table('lesson_blocks', function (Blueprint $table) {
            $table->enum('block_type', ['text', 'image', 'file', 'embed'])
                ->default('text')
                ->after('lesson_id');
        });
    }
};
