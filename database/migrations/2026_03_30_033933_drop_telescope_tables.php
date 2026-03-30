<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop Telescope tables (dev/debugging only, not needed in production)
        Schema::dropIfExists('telescope_entries_tags');
        Schema::dropIfExists('telescope_entries');
        Schema::dropIfExists('telescope_monitoring');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Telescope tables can be recreated by running: php artisan telescope:install
        // We don't recreate them here as they are dev-only tables
    }
};
