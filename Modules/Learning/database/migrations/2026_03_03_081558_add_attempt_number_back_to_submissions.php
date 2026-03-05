<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('submissions', 'attempt_number')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->integer('attempt_number')->default(1)->after('user_id');
            });
        }

        if (! $this->indexExists('submissions', 'submissions_assignment_id_user_id_attempt_number_index')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->index(['assignment_id', 'user_id', 'attempt_number']);
            });
        }

        if (! Schema::hasColumn('quiz_submissions', 'attempt_number')) {
            Schema::table('quiz_submissions', function (Blueprint $table) {
                $table->integer('attempt_number')->default(1)->after('user_id');
            });
        }

        if (! $this->indexExists('quiz_submissions', 'quiz_submissions_quiz_id_user_id_attempt_number_index')) {
            Schema::table('quiz_submissions', function (Blueprint $table) {
                $table->index(['quiz_id', 'user_id', 'attempt_number']);
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('submissions', 'submissions_assignment_id_user_id_attempt_number_index')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->dropIndex(['assignment_id', 'user_id', 'attempt_number']);
            });
        }

        if (Schema::hasColumn('submissions', 'attempt_number')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->dropColumn('attempt_number');
            });
        }

        if ($this->indexExists('quiz_submissions', 'quiz_submissions_quiz_id_user_id_attempt_number_index')) {
            Schema::table('quiz_submissions', function (Blueprint $table) {
                $table->dropIndex(['quiz_id', 'user_id', 'attempt_number']);
            });
        }

        if (Schema::hasColumn('quiz_submissions', 'attempt_number')) {
            Schema::table('quiz_submissions', function (Blueprint $table) {
                $table->dropColumn('attempt_number');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $schemaName = $connection->getConfig('schema') ?: 'public';

        $result = $connection->selectOne(
            'SELECT EXISTS (
                SELECT 1 
                FROM pg_indexes 
                WHERE schemaname = ? 
                AND tablename = ? 
                AND indexname = ?
            ) as exists',
            [$schemaName, $table, $indexName]
        );

        return (bool) $result->exists;
    }
};
