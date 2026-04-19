<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        
        DB::statement('ALTER TABLE points DROP CONSTRAINT IF EXISTS points_reason_check');

        
        DB::statement('ALTER TABLE points DROP CONSTRAINT IF EXISTS points_source_type_check');
    }

    public function down(): void
    {
        
        
    }
};
