<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old unique constraint
        DB::statement('ALTER TABLE user_event_counters DROP CONSTRAINT IF EXISTS user_event_counter_unique');
        
        // Create a new unique constraint that matches the ON CONFLICT clause
        // Using COALESCE to handle NULL values consistently
        // Note: "window" is a reserved keyword in PostgreSQL, so it must be quoted
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
        // Drop the new unique index
        DB::statement('DROP INDEX IF EXISTS user_event_counter_unique');
        
        // Recreate the old unique constraint (if needed)
        Schema::table('user_event_counters', function ($table) {
            $table->unique(
                ['user_id', 'event_type', 'scope_type', 'scope_id', 'window', 'window_start'], 
                'user_event_counter_unique'
            );
        });
    }
};
