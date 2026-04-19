<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        
        if (Schema::hasColumn('users', 'avatar_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('avatar_path');
            });
        }

        
        if (Schema::hasColumn('courses', 'thumbnail_path')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('thumbnail_path');
            });
        }
        if (Schema::hasColumn('courses', 'banner_path')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('banner_path');
            });
        }

        
        if (Schema::hasColumn('news', 'featured_image_path')) {
            Schema::table('news', function (Blueprint $table) {
                $table->dropColumn('featured_image_path');
            });
        }

        
        if (Schema::hasColumn('lesson_blocks', 'media_url')) {
            Schema::table('lesson_blocks', function (Blueprint $table) {
                $table->dropColumn('media_url');
            });
        }
        if (Schema::hasColumn('lesson_blocks', 'media_thumbnail_url')) {
            Schema::table('lesson_blocks', function (Blueprint $table) {
                $table->dropColumn('media_thumbnail_url');
            });
        }
        if (Schema::hasColumn('lesson_blocks', 'media_meta_json')) {
            Schema::table('lesson_blocks', function (Blueprint $table) {
                $table->dropColumn('media_meta_json');
            });
        }

        
        if (Schema::hasColumn('badges', 'icon_path')) {
            Schema::table('badges', function (Blueprint $table) {
                $table->dropColumn('icon_path');
            });
        }

        
        if (Schema::hasColumn('certificates', 'file_path')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->dropColumn('file_path');
            });
        }

        
        if (Schema::hasColumn('submission_files', 'file_path')) {
            Schema::table('submission_files', function (Blueprint $table) {
                $table->dropColumn('file_path');
            });
        }
        if (Schema::hasColumn('submission_files', 'file_name')) {
            Schema::table('submission_files', function (Blueprint $table) {
                $table->dropColumn('file_name');
            });
        }
        if (Schema::hasColumn('submission_files', 'file_size')) {
            Schema::table('submission_files', function (Blueprint $table) {
                $table->dropColumn('file_size');
            });
        }

        
        if (Schema::hasColumn('reports', 'file_path')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->dropColumn('file_path');
            });
        }
    }

    
    public function down(): void
    {
        
        if (! Schema::hasColumn('users', 'avatar_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('avatar_path')->nullable()->after('remember_token');
            });
        }

        
        if (! Schema::hasColumn('courses', 'thumbnail_path')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->string('thumbnail_path')->nullable();
            });
        }
        if (! Schema::hasColumn('courses', 'banner_path')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->string('banner_path')->nullable();
            });
        }

        
        if (! Schema::hasColumn('news', 'featured_image_path')) {
            Schema::table('news', function (Blueprint $table) {
                $table->string('featured_image_path')->nullable();
            });
        }

        
        if (! Schema::hasColumn('lesson_blocks', 'media_url')) {
            Schema::table('lesson_blocks', function (Blueprint $table) {
                $table->string('media_url')->nullable();
            });
        }
        if (! Schema::hasColumn('lesson_blocks', 'media_thumbnail_url')) {
            Schema::table('lesson_blocks', function (Blueprint $table) {
                $table->string('media_thumbnail_url')->nullable();
            });
        }
        if (! Schema::hasColumn('lesson_blocks', 'media_meta_json')) {
            Schema::table('lesson_blocks', function (Blueprint $table) {
                $table->json('media_meta_json')->nullable();
            });
        }

        
        if (! Schema::hasColumn('badges', 'icon_path')) {
            Schema::table('badges', function (Blueprint $table) {
                $table->string('icon_path')->nullable();
            });
        }

        
        if (! Schema::hasColumn('certificates', 'file_path')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->string('file_path')->nullable();
            });
        }

        
        if (! Schema::hasColumn('submission_files', 'file_path')) {
            Schema::table('submission_files', function (Blueprint $table) {
                $table->string('file_path')->nullable();
            });
        }
        if (! Schema::hasColumn('submission_files', 'file_name')) {
            Schema::table('submission_files', function (Blueprint $table) {
                $table->string('file_name')->nullable();
            });
        }
        if (! Schema::hasColumn('submission_files', 'file_size')) {
            Schema::table('submission_files', function (Blueprint $table) {
                $table->unsignedBigInteger('file_size')->nullable();
            });
        }

        
        if (! Schema::hasColumn('reports', 'file_path')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->string('file_path')->nullable();
            });
        }
    }
};
