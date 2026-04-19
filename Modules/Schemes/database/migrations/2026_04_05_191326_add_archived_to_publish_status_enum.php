<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        
        DB::statement("ALTER TYPE publish_status ADD VALUE IF NOT EXISTS 'archived'");
    }

    
    public function down(): void
    {
        
        
        
    }
};
