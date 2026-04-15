<?php

// Quick test to see what's failing in the master_data migration

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "Testing master_data table creation...\n";
    
    // Drop if exists
    DB::statement('DROP TABLE IF EXISTS master_data CASCADE');
    echo "✓ Dropped existing table\n";
    
    // Try creating with basic structure first
    DB::statement('CREATE TABLE master_data (
        id BIGSERIAL PRIMARY KEY,
        type VARCHAR(50),
        value VARCHAR(100),
        label VARCHAR(255),
        metadata JSONB,
        is_system BOOLEAN DEFAULT false,
        is_active BOOLEAN DEFAULT true,
        sort_order INTEGER DEFAULT 0,
        created_at TIMESTAMP,
        updated_at TIMESTAMP
    )');
    echo "✓ Created table with basic structure\n";
    
    // Try adding index
    DB::statement('CREATE INDEX master_data_type_index ON master_data(type)');
    echo "✓ Added type index\n";
    
    // Try adding unique constraint
    DB::statement('ALTER TABLE master_data ADD CONSTRAINT master_data_type_value_unique UNIQUE (type, value)');
    echo "✓ Added unique constraint\n";
    
    // Try adding composite index
    DB::statement('CREATE INDEX master_data_type_is_active_index ON master_data(type, is_active)');
    echo "✓ Added composite index\n";
    
    echo "\n✅ All tests passed! The migration should work.\n";
    
    // Clean up
    DB::statement('DROP TABLE master_data CASCADE');
    echo "✓ Cleaned up test table\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
