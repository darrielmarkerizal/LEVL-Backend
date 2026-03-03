<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop old specialization column if exists
            if (Schema::hasColumn('users', 'specialization')) {
                $table->dropColumn('specialization');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            // Add new specialization_id as foreign key
            $table->foreignId('specialization_id')
                ->nullable()
                ->after('bio')
                ->constrained('categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'specialization_id')) {
                $table->dropForeign(['specialization_id']);
                $table->dropColumn('specialization_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            // Restore old specialization column
            $table->string('specialization', 100)->nullable()->after('bio');
        });
    }
};
