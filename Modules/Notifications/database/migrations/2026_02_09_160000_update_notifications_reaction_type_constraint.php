<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Notifications\Enums\NotificationType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old constraint
        DB::statement("ALTER TABLE notifications DROP CONSTRAINT IF EXISTS notifications_type_check");

        // Get all enum values
        $types = array_map(fn($type) => "'$type'", NotificationType::values());
        $typeString = implode(',', $types);

        // Add the new constraint with all current enum values
        // We use the simpler IN syntax which is standard SQL and works in Postgres
        DB::statement("ALTER TABLE notifications ADD CONSTRAINT notifications_type_check CHECK (type IN ($typeString))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Dropping the constraint is safe enough for down, 
        // or we could restore the previous list if strictly necessary.
        // For now, we'll just drop it to avoid errors if we roll back.
        DB::statement("ALTER TABLE notifications DROP CONSTRAINT IF EXISTS notifications_type_check");
        
        // Optionally, we could try to restore the *previous* state, but that requires hardcoding the old list again.
        // Given this is a development fix, leaving it without a strict revert to the exact previous state is usually acceptable 
        // unless we need to strictly roll back to a specific schema version for compatibility.
        // But to be safe and allow rollback to "work" (empty down or loose constraint), we can just leave it dropped or re-add a loose one.
        // A better approach for strict rollback would be to list the previous values again.
        
        // For this task, getting it working is priority.
    }
};
