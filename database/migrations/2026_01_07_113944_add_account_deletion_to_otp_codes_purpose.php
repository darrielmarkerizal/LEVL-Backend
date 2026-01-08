<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE otp_codes DROP CONSTRAINT IF EXISTS otp_codes_purpose_check");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE otp_codes ADD CONSTRAINT otp_codes_purpose_check CHECK (purpose::text = ANY (ARRAY['register_verification'::text, 'password_reset'::text, 'email_change_verification'::text, 'two_factor_auth'::text, 'account_deletion'::text]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE otp_codes DROP CONSTRAINT IF EXISTS otp_codes_purpose_check");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE otp_codes ADD CONSTRAINT otp_codes_purpose_check CHECK (purpose::text = ANY (ARRAY['register_verification'::text, 'password_reset'::text, 'email_change_verification'::text, 'two_factor_auth'::text]))");
    }
};
