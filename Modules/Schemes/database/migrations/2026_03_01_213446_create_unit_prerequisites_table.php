<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('prerequisite_id')->constrained('units')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['unit_id', 'prerequisite_id'], 'uniq_unit_prerequisite');
            $table->index('unit_id', 'idx_unit_prereq_unit');
            $table->index('prerequisite_id', 'idx_unit_prereq_prerequisite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_prerequisites');
    }
};
