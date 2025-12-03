<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add the new enrollment_key_hash column
        Schema::table('courses', function (Blueprint $table) {
            $table->string('enrollment_key_hash', 255)->nullable()->after('enrollment_type');
        });

        // Step 2: Migrate existing plain text keys to hashed values
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

        // Step 3: Remove the old enrollment_key column
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('enrollment_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Add back the enrollment_key column
        Schema::table('courses', function (Blueprint $table) {
            $table->string('enrollment_key', 100)->nullable()->after('enrollment_type');
        });

        // Note: We cannot restore the original plain text keys from hashes
        // The enrollment_key will be null after rollback

        // Step 2: Remove the enrollment_key_hash column
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('enrollment_key_hash');
        });
    }
};
