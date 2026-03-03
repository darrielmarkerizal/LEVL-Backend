<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_id')->nullable()->after('id');
            $table->integer('order')->default(0)->after('unit_id');
            $table->index(['unit_id', 'order']);
        });

        DB::statement('UPDATE assignments SET unit_id = (SELECT unit_id FROM lessons WHERE lessons.id = assignments.lesson_id) WHERE lesson_id IS NOT NULL');

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['lesson_id', 'assignable_type', 'assignable_id']);
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->unsignedBigInteger('unit_id')->nullable()->after('id');
            $table->integer('order')->default(0)->after('unit_id');
            $table->index(['unit_id', 'order']);
        });

        DB::statement('UPDATE quizzes SET unit_id = (SELECT unit_id FROM lessons WHERE lessons.id = quizzes.lesson_id) WHERE lesson_id IS NOT NULL');

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['lesson_id', 'assignable_type', 'assignable_id']);
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->unsignedBigInteger('lesson_id')->nullable();
            $table->string('assignable_type')->nullable();
            $table->unsignedBigInteger('assignable_id')->nullable();
            $table->dropColumn(['unit_id', 'order']);
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->unsignedBigInteger('lesson_id')->nullable();
            $table->string('assignable_type')->nullable();
            $table->unsignedBigInteger('assignable_id')->nullable();
            $table->dropColumn(['unit_id', 'order']);
        });
    }
};
