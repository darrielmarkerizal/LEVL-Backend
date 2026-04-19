<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            
            
            
            
            DB::statement("
                UPDATE audit_logs 
                SET subject_type = target_type, subject_id = target_id 
                WHERE subject_type IS NULL AND target_type IS NOT NULL
            ");

            
            $table->dropColumn(['target_type', 'target_id', 'event']);
        });
    }

    
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            
            $table->string('target_type')->nullable()->after('actor_id');
            $table->unsignedBigInteger('target_id')->nullable()->after('target_type');
            $table->string('event')->nullable()->after('id');
        });

        
        DB::statement("
            UPDATE audit_logs 
            SET target_type = subject_type, target_id = subject_id 
            WHERE subject_type IS NOT NULL
        ");
    }
};
