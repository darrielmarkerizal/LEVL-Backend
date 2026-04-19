<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE badges DROP CONSTRAINT IF EXISTS badges_type_check');
    }

    
    public function down(): void
    {
        
    }
};
