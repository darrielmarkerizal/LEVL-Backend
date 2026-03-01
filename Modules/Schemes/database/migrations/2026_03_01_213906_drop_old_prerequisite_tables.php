<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('lesson_prerequisites');
        Schema::dropIfExists('unit_prerequisites');
    }

    public function down(): void
    {
        Schema::create('lesson_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->foreignId('prerequisite_id')->constrained('lessons')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['lesson_id', 'prerequisite_id'], 'uniq_lesson_prerequisite');
        });

        Schema::create('unit_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('prerequisite_id')->constrained('units')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['unit_id', 'prerequisite_id'], 'uniq_unit_prerequisite');
        });
    }
};
