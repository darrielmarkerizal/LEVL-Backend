<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn([
                'deadline_at',
                'available_from',
                'tolerance_minutes',
                'late_penalty_percent',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->timestamp('available_from')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->integer('tolerance_minutes')->default(0);
            $table->integer('late_penalty_percent')->default(0);
        });
    }
};
