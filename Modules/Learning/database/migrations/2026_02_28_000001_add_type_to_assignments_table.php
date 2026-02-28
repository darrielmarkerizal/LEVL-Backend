<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->enum('type', ['assignment', 'quiz'])
                ->default('assignment')
                ->after('description')
                ->comment('Type of assignment: assignment (file upload) or quiz (questions)');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
