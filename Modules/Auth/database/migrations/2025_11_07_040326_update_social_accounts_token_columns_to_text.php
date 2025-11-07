<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `social_accounts` MODIFY `token` TEXT NULL');
        DB::statement('ALTER TABLE `social_accounts` MODIFY `refresh_token` TEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        DB::statement('ALTER TABLE `social_accounts` MODIFY `token` VARCHAR(255) NULL');
        DB::statement('ALTER TABLE `social_accounts` MODIFY `refresh_token` VARCHAR(255) NULL');
    }
};
