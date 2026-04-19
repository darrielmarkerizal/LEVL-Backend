<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        
        Schema::table('courses', function (Blueprint $table) {
            $table->string('enrollment_key_hash', 255)->nullable()->after('enrollment_type');
        });

        
        DB::table('courses')
            ->whereNotNull('enrollment_key')
            ->where('enrollment_key', '!=', '')
            ->orderBy('id')
            ->chunk(100, function ($courses) {
                foreach ($courses as $course) {
                    DB::table('courses')
                        ->where('id', $course->id)
                        ->update([
                            'enrollment_key_hash' => Hash::make($course->enrollment_key),
                        ]);
                }
            });

        
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('enrollment_key');
        });
    }

    
    public function down(): void
    {
        
        Schema::table('courses', function (Blueprint $table) {
            $table->string('enrollment_key', 100)->nullable()->after('enrollment_type');
        });

        
        

        
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('enrollment_key_hash');
        });
    }
};
