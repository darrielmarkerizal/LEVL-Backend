<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('trash_bins', function (Blueprint $table) {
            $table->id();
            $table->string('resource_type', 64);
            $table->string('trashable_type');
            $table->unsignedBigInteger('trashable_id');
            $table->uuid('group_uuid')->index();
            $table->string('root_resource_type')->nullable();
            $table->unsignedBigInteger('root_resource_id')->nullable();
            $table->string('original_status')->nullable();
            $table->string('trashed_status')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('deleted_at');
            $table->timestamp('expires_at');
            $table->json('metadata')->nullable();
            $table->timestamp('restored_at')->nullable();
            $table->timestamp('force_deleted_at')->nullable();
            $table->timestamps();

            $table->unique(['trashable_type', 'trashable_id'], 'trash_bins_unique_trashable');
            $table->index(['resource_type', 'deleted_at']);
            $table->index('expires_at');
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('trash_bins');
    }
};
