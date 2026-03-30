<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop content_workflow_history table (redundant with content_revisions)
        Schema::dropIfExists('content_workflow_history');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate content_workflow_history table
        Schema::create('content_workflow_history', function ($table) {
            $table->id();
            $table->string('content_type');
            $table->unsignedBigInteger('content_id');
            $table->string('from_state')->nullable();
            $table->string('to_state');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['content_type', 'content_id']);
        });
    }
};
