<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->integer('attempt_number')->default(1)->after('user_id');
            $table->index(['assignment_id', 'user_id', 'attempt_number']);
        });

        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->integer('attempt_number')->default(1)->after('user_id');
            $table->index(['quiz_id', 'user_id', 'attempt_number']);
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex(['assignment_id', 'user_id', 'attempt_number']);
            $table->dropColumn('attempt_number');
        });

        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->dropIndex(['quiz_id', 'user_id', 'attempt_number']);
            $table->dropColumn('attempt_number');
        });
    }
};
