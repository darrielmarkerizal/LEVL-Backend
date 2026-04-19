<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('points', function (Blueprint $table) {
            
            

            
            $table->unique(['user_id', 'source_type', 'source_id', 'reason'], 'points_unique_transaction');
        });
    }

    
    public function down(): void
    {
        Schema::table('points', function (Blueprint $table) {
            $table->dropUnique('points_unique_transaction');
        });
    }
};
