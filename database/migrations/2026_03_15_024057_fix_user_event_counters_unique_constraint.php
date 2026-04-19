<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        
        DB::statement('ALTER TABLE user_event_counters DROP CONSTRAINT IF EXISTS user_event_counter_unique');

        
        
        
        DB::statement('
            CREATE UNIQUE INDEX user_event_counter_unique 
            ON user_event_counters (
                user_id, 
                event_type, 
                COALESCE(scope_type, \'\'), 
                COALESCE(scope_id, 0), 
                "window"
            )
        ');
    }

    public function down(): void
    {
        
        DB::statement('DROP INDEX IF EXISTS user_event_counter_unique');

        
        Schema::table('user_event_counters', function ($table) {
            $table->unique(
                ['user_id', 'event_type', 'scope_type', 'scope_id', 'window', 'window_start'],
                'user_event_counter_unique'
            );
        });
    }
};
