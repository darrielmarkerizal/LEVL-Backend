<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE quiz_answers ALTER COLUMN is_auto_graded DROP DEFAULT');
        DB::statement('ALTER TABLE quiz_answers ALTER COLUMN is_auto_graded TYPE integer USING is_auto_graded::integer');
        DB::statement('ALTER TABLE quiz_answers ALTER COLUMN is_auto_graded SET DEFAULT 0');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE quiz_answers ALTER COLUMN is_auto_graded DROP DEFAULT');
        DB::statement('ALTER TABLE quiz_answers ALTER COLUMN is_auto_graded TYPE boolean USING is_auto_graded::boolean');
        DB::statement('ALTER TABLE quiz_answers ALTER COLUMN is_auto_graded SET DEFAULT false');
    }
};
