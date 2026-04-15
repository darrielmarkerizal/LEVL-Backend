<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Disable transaction supaya error asli kelihatan di PostgreSQL
     */
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_data', function (Blueprint $table) {
            $table->id();

            $table->string('type', 50);
            $table->string('value', 100);
            $table->string('label', 255);

            $table->json('metadata')->nullable();

            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);

            // PostgreSQL tidak punya unsigned integer → pakai integer biasa
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // kasih nama constraint manual biar aman
            $table->unique(['type', 'value'], 'md_type_value_unique');

            // index tambahan
            $table->index(['type', 'is_active'], 'md_type_active_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_data');
    }
};