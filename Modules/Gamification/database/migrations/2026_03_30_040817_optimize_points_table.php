<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('points', function (Blueprint $table) {
            
            if (Schema::hasColumn('points', 'triggered_level_up')) {
                $table->dropColumn('triggered_level_up');
            }
        });
    }

    
    public function down(): void
    {
        Schema::table('points', function (Blueprint $table) {
            
            $table->boolean('triggered_level_up')->default(false)->after('new_level');
        });
    }
};
