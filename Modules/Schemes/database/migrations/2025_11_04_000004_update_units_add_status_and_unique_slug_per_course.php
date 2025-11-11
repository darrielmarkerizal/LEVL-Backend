<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('units')) {
            Schema::table('units', function (Blueprint $table) {

                $table->dropUnique(['slug']);

                if (! Schema::hasColumn('units', 'status')) {
                    $table->enum('status', ['draft', 'published'])->default('draft')->after('description');
                }

                $table->unique(['course_id', 'slug'], 'units_course_id_slug_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('units')) {
            Schema::table('units', function (Blueprint $table) {

                $table->dropUnique('units_course_id_slug_unique');

                if (Schema::hasColumn('units', 'status')) {
                    $table->dropColumn('status');
                }

                $table->unique('slug');
            });
        }
    }
};
