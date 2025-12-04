<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE points MODIFY COLUMN source_type ENUM('lesson', 'assignment', 'attempt', 'challenge', 'system') DEFAULT 'system'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE points MODIFY COLUMN source_type ENUM('lesson', 'assignment', 'attempt', 'system') DEFAULT 'system'");
    }
};
