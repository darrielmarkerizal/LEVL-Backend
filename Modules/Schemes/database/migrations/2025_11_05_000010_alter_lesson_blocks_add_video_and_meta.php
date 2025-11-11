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

        $platform = DB::getDriverName();
        if ($platform === 'mysql') {
            DB::statement("ALTER TABLE lesson_blocks MODIFY block_type ENUM('text','image','file','embed','video') NOT NULL DEFAULT 'text'");
        }
    }

    public function down(): void
    {
        $platform = DB::getDriverName();
        if ($platform === 'mysql') {
            DB::statement("ALTER TABLE lesson_blocks MODIFY block_type ENUM('text','image','file','embed') NOT NULL DEFAULT 'text'");
        }

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
