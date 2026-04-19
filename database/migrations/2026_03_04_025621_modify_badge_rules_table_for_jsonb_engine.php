<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::table('badge_rules')->truncate();

        Schema::table('badge_rules', function (Blueprint $table) {
            $table->dropColumn(['criterion', 'operator', 'value']);
            $table->string('event_trigger')->after('badge_id');
            $table->jsonb('conditions')->nullable()->after('event_trigger');
        });
    }

    
    public function down(): void
    {
        Schema::table('badge_rules', function (Blueprint $table) {
            $table->dropColumn(['event_trigger', 'conditions']);
            $table->string('criterion');
            $table->string('operator');
            $table->integer('value');
        });
    }
};
