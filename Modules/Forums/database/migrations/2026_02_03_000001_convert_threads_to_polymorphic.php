<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->string('forumable_type')->after('id')->default('Modules\\Schemes\\Models\\Course');
            $table->unsignedBigInteger('forumable_id')->after('forumable_type')->default(0);

            $table->index(['forumable_type', 'forumable_id']);
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->dropForeign(['scheme_id']);
            $table->dropIndex(['scheme_id', 'last_activity_at']);
            $table->dropColumn('scheme_id');
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->index(['forumable_type', 'forumable_id', 'last_activity_at']);
            $table->index(['forumable_type', 'forumable_id', 'is_pinned']);
        });
    }

    public function down(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropIndex(['forumable_type', 'forumable_id', 'is_pinned']);
            $table->dropIndex(['forumable_type', 'forumable_id', 'last_activity_at']);
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->dropIndex(['forumable_type', 'forumable_id']);
            $table->dropColumns(['forumable_type', 'forumable_id']);
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->foreignId('scheme_id')->constrained('courses')->onDelete('cascade');
            $table->index(['scheme_id', 'last_activity_at']);
        });
    }
};
