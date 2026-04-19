<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
        });
    }

    
    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->string('category', 50)->nullable()->after('type');
            $table->index('category');
        });
    }
};
