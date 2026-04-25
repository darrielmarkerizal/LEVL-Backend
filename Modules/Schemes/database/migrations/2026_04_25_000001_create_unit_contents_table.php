<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->string('contentable_type', 50);
            $table->unsignedBigInteger('contentable_id');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['unit_id', 'order'], 'uq_unit_contents_unit_order');
            $table->unique(['contentable_type', 'contentable_id'], 'uq_unit_contents_contentable');
            $table->index(['unit_id', 'order'], 'idx_unit_contents_unit_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_contents');
    }
};
