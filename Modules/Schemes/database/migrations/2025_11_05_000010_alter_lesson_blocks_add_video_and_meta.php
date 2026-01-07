<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_blocks', function (Blueprint $table) {
            if (! Schema::hasColumn('lesson_blocks', 'media_thumbnail_url')) {
                $table->string('media_thumbnail_url')->nullable()->after('media_url');
            }
            if (! Schema::hasColumn('lesson_blocks', 'media_meta_json')) {
                $table->json('media_meta_json')->nullable()->after('media_thumbnail_url');
            }
        });

        Schema::table('lesson_blocks', function (Blueprint $table) {
            // Using raw SQL for PostgreSQL compatibility because doctrine/dbal has issues with altering enums/checks
            if (DB::getDriverName() === 'pgsql') {
                 // Check if the constraint exists before dropping it (robustness)
                 // Usually Laravel names it lesson_blocks_block_type_check
                 DB::statement("ALTER TABLE lesson_blocks DROP CONSTRAINT IF EXISTS lesson_blocks_block_type_check");
                 DB::statement("ALTER TABLE lesson_blocks ADD CONSTRAINT lesson_blocks_block_type_check CHECK (block_type::text IN ('text', 'image', 'file', 'embed', 'video'))");
            } else {
                 $table->enum('block_type', ['text', 'image', 'file', 'embed', 'video'])->default('text')->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('lesson_blocks', function (Blueprint $table) {
            if (DB::getDriverName() === 'pgsql') {
                 DB::statement("ALTER TABLE lesson_blocks DROP CONSTRAINT IF EXISTS lesson_blocks_block_type_check");
                 DB::statement("ALTER TABLE lesson_blocks ADD CONSTRAINT lesson_blocks_block_type_check CHECK (block_type::text IN ('text', 'image', 'file', 'embed'))");
            } else {
                $table->enum('block_type', ['text', 'image', 'file', 'embed'])->default('text')->change();
            }
        });

        Schema::table('lesson_blocks', function (Blueprint $table) {
            if (Schema::hasColumn('lesson_blocks', 'media_thumbnail_url')) {
                $table->dropColumn('media_thumbnail_url');
            }
            if (Schema::hasColumn('lesson_blocks', 'media_meta_json')) {
                $table->dropColumn('media_meta_json');
            }
        });
    }
};
