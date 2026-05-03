<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->string('session_token', 64)->nullable()->after('enrollment_id');
            $table->index('session_token', 'idx_quiz_submissions_session_token');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->dropIndex('idx_quiz_submissions_session_token');
            $table->dropColumn('session_token');
        });
    }
};
