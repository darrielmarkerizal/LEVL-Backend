<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('answers') && Schema::hasColumn('answers', 'question_id')) {
            Schema::table('answers', function (Blueprint $table) {
                $table->dropForeign(['question_id']);
                $table->dropColumn('question_id');
            });
        }

        if (Schema::hasTable('assignment_questions')) {
            Schema::dropIfExists('assignment_questions');
        }

        if (Schema::hasColumn('assignments', 'type')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('assignments', 'type')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->string('type', 50)->default('assignment')->after('id');
            });
        }

        if (! Schema::hasTable('assignment_questions')) {
            Schema::create('assignment_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assignment_id')->constrained()->onDelete('cascade');
                $table->string('type', 50);
                $table->text('content');
                $table->json('options')->nullable();
                $table->json('answer_key')->nullable();
                $table->decimal('weight', 5, 2)->default(1.00);
                $table->integer('order')->default(0);
                $table->decimal('max_score', 8, 2)->nullable();
                $table->timestamps();

                $table->index('assignment_id', 'idx_assignment_questions_assignment');
                $table->index('type', 'idx_assignment_questions_type');
                $table->index('order', 'idx_assignment_questions_order');
            });
        }

        if (Schema::hasTable('answers') && ! Schema::hasColumn('answers', 'question_id')) {
            Schema::table('answers', function (Blueprint $table) {
                $table->foreignId('question_id')->after('submission_id')->constrained('assignment_questions')->onDelete('cascade');
                $table->index('question_id', 'idx_answers_question');
            });
        }
    }
};
