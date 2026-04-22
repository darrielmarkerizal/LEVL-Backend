<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('assignments', 'time_limit_minutes')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->dropColumn('time_limit_minutes');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('assignments', 'time_limit_minutes')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->integer('time_limit_minutes')->nullable()->after('status');
            });
        }
    }
};
