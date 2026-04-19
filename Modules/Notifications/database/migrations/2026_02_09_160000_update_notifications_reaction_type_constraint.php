<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Notifications\Enums\NotificationType;

return new class extends Migration
{
    
    public function up(): void
    {
        
        DB::statement('ALTER TABLE notifications DROP CONSTRAINT IF EXISTS notifications_type_check');

        
        $types = array_map(fn ($type) => "'$type'", NotificationType::values());
        $typeString = implode(',', $types);

        
        
        DB::statement("ALTER TABLE notifications ADD CONSTRAINT notifications_type_check CHECK (type IN ($typeString))");
    }

    
    public function down(): void
    {
        
        
        
        DB::statement('ALTER TABLE notifications DROP CONSTRAINT IF EXISTS notifications_type_check');

        
        
        
        
        

        
    }
};
