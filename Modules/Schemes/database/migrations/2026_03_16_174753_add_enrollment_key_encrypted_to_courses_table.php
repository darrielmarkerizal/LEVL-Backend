<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Add encrypted enrollment key column
            $table->text('enrollment_key_encrypted')->nullable()->after('enrollment_key_hash');
        });

        // Migrate existing hashed keys - Note: We cannot decrypt hashed values
        // Existing courses with key_based enrollment will need to regenerate their keys
        // or we can keep both columns temporarily for backward compatibility
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('enrollment_key_encrypted');
        });
    }
};
