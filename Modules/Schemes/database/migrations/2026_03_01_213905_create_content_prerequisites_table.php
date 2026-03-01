<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->morphs('content');
            $table->morphs('prerequisite');
            $table->timestamps();

            $table->unique(['content_type', 'content_id', 'prerequisite_type', 'prerequisite_id'], 'uniq_content_prerequisite');
            $table->index(['content_type', 'content_id'], 'idx_content');
            $table->index(['prerequisite_type', 'prerequisite_id'], 'idx_prerequisite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_prerequisites');
    }
};
