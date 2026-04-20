<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->boolean('auto_activate_on_enrolled_at')
                ->default(false)
                ->after('enrolled_at');

            $table->index('auto_activate_on_enrolled_at', 'enrollments_auto_activate_idx');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex('enrollments_auto_activate_idx');
            $table->dropColumn('auto_activate_on_enrolled_at');
        });
    }
};
