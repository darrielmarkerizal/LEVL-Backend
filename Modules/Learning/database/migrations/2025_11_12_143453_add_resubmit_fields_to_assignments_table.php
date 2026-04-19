<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->boolean('allow_resubmit')->nullable()->after('status');
            $table->integer('late_penalty_percent')->nullable()->after('allow_resubmit');
        });
    }

    
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['late_penalty_percent', 'allow_resubmit']);
        });
    }
};
