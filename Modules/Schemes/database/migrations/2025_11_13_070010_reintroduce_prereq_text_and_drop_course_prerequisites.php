<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('courses') && ! Schema::hasColumn('courses', 'prereq_text')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->text('prereq_text')->nullable()->after('tags_json');
            });
        }

        if (Schema::hasTable('course_prerequisites')) {
            Schema::dropIfExists('course_prerequisites');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('course_prerequisites')) {
            Schema::create('course_prerequisites', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
                $table->foreignId('prerequisite_course_id')->constrained('courses')->onDelete('cascade');
                $table->boolean('is_required')->default(true);
                $table->timestamps();

                $table->unique(['course_id', 'prerequisite_course_id']);
                $table->index('course_id');
            });
        }

        if (Schema::hasTable('courses') && Schema::hasColumn('courses', 'prereq_text')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('prereq_text');
            });
        }
    }
};
