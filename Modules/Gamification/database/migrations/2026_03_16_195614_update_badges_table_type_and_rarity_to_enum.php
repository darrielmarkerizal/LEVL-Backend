<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        
        
        DB::table('badges')->where('type', 'achievement')->update(['type' => 'completion']);

        
        DB::statement('ALTER TABLE badges DROP CONSTRAINT IF EXISTS badges_type_check');

        
        DB::statement('ALTER TABLE badges ALTER COLUMN type TYPE VARCHAR(50)');

        
        DB::statement("
            ALTER TABLE badges 
            ADD CONSTRAINT badges_type_check 
            CHECK (type IN ('completion', 'quality', 'speed', 'habit', 'social', 'milestone', 'hidden'))
        ");

        
        DB::table('badges')->whereNull('type')->update(['type' => 'completion']);
    }

    
    public function down(): void
    {
        
        
        DB::table('badges')->where('type', 'completion')->update(['type' => 'achievement']);
        DB::table('badges')->where('type', 'hidden')->update(['type' => 'achievement']);
        DB::table('badges')->whereIn('type', ['quality', 'speed', 'habit', 'social'])->update(['type' => 'achievement']);

        
        DB::statement('ALTER TABLE badges DROP CONSTRAINT IF EXISTS badges_type_check');

        
        DB::statement("
            ALTER TABLE badges 
            ADD CONSTRAINT badges_type_check 
            CHECK (type IN ('achievement', 'milestone', 'completion'))
        ");
    }
};
