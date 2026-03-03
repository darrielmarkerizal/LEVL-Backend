<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['activity', 'assessment', 'grading', 'system', 'custom'])->default('activity');
            $table->foreignId('generated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('filters')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();

            $table->index(['type', 'generated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
