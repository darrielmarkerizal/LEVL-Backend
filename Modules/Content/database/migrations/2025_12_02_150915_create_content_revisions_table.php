<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('content_revisions', function (Blueprint $table) {
            $table->id();
            $table->string('content_type');
            $table->unsignedBigInteger('content_id');
            $table->foreignId('editor_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->text('revision_note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            
            $table->index(['content_type', 'content_id', 'created_at']);
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('content_revisions');
    }
};
