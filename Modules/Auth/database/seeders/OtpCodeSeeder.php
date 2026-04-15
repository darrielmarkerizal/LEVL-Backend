<?php

declare(strict_types=1);

namespace Modules\Auth\Database\Seeders;

use App\Support\RealisticSeederContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;

class OtpCodeSeeder extends Seeder
{
    private const CHUNK_SIZE = 100;

    public function run(): void
    {
        $this->command->info("\n🔐 Creating OTP codes...");

        $totalCodes = 0;

        $totalCodes += $this->createEmailVerificationCodes();
        $totalCodes += $this->createPasswordResetCodes();
        $totalCodes += $this->createEmailChangeCodes();
        $totalCodes += $this->createAccountDeletionCodes();

        $this->command->info("\n✅ Created {$totalCodes} OTP codes");
    }

    private function createEmailVerificationCodes(): int
    {
        $this->command->info('  📧 Creating email verification codes...');

        $pendingUsers = User::where('status', 'pending')->get();
        $codes = [];

        foreach ($pendingUsers as $user) {
            $codes[] = [
                'uuid' => RealisticSeederContent::stableUuid($user->id + 7000),
                'user_id' => $user->id,
                'channel' => 'email',
                'provider' => 'mailhog',
                'purpose' => 'register_verification',
                'code' => 'magic',
                'meta' => json_encode([
                    'token_hash' => hash('sha256', \Illuminate\Support\Str::random(16)),
                ]),
                'expires_at' => now()->addMinutes(60),
                'consumed_at' => null,
                'created_at' => now()->subMinutes(5 + ($user->id % 26)),
                'updated_at' => now()->subMinutes(5 + ($user->id % 26)),
            ];
        }

        $this->batchInsertCodes($codes);
        $this->command->info('    ✓ Created '.count($codes).' email verification codes');

        return count($codes);
    }

    private function createPasswordResetCodes(): int
    {
        $this->command->info('  🔑 Creating password reset codes...');

        $activeUserCount = User::where('status', 'active')->count();
        $targetCount = (int) ($activeUserCount * 0.05);

        $activeUsers = User::where('status', 'active')
            ->inRandomOrder()
            ->limit($targetCount)
            ->get();

        $codes = [];

        foreach ($activeUsers as $user) {
            $codes[] = [
                'uuid' => RealisticSeederContent::stableUuid($user->id + 8000),
                'user_id' => $user->id,
                'channel' => 'email',
                'provider' => 'mailhog',
                'purpose' => 'password_reset',
                'code' => 'magic',
                'meta' => json_encode([
                    'token_hash' => hash('sha256', \Illuminate\Support\Str::random(64)),
                ]),
                'expires_at' => now()->addHour(),
                'consumed_at' => null,
                'created_at' => now()->subHours(1 + ($user->id % 23)),
                'updated_at' => now()->subHours(1 + ($user->id % 23)),
            ];
        }

        $this->batchInsertCodes($codes);
        $this->command->info('    ✓ Created '.count($codes).' password reset codes');

        return count($codes);
    }

    private function createEmailChangeCodes(): int
    {
        $this->command->info('  ✉️  Creating email change verification codes...');

        $activeUserCount = User::where('status', 'active')->count();
        $targetCount = (int) ($activeUserCount * 0.03);

        $users = User::where('status', 'active')
            ->inRandomOrder()
            ->limit($targetCount)
            ->get();

        $codes = [];

        foreach ($users as $user) {
            $newEmail = RealisticSeederContent::pendingEmailChange($user->id);

            $codes[] = [
                'uuid' => RealisticSeederContent::stableUuid($user->id + 9000),
                'user_id' => $user->id,
                'channel' => 'email',
                'provider' => 'mailhog',
                'purpose' => 'email_change_verification',
                'code' => 'magic',
                'meta' => json_encode([
                    'token_hash' => hash('sha256', \Illuminate\Support\Str::random(16)),
                    'new_email' => $newEmail,
                ]),
                'expires_at' => now()->addHour(),
                'consumed_at' => null,
                'created_at' => now()->subHours(1 + ($user->id % 12)),
                'updated_at' => now()->subHours(1 + ($user->id % 12)),
            ];
        }

        $this->batchInsertCodes($codes);
        $this->command->info('    ✓ Created '.count($codes).' email change codes');

        return count($codes);
    }

    private function createAccountDeletionCodes(): int
    {
        $this->command->info('  🗑️  Creating account deletion codes...');

        $activeUserCount = User::where('status', 'active')->count();
        $targetCount = (int) ($activeUserCount * 0.01);

        $users = User::where('status', 'active')
            ->inRandomOrder()
            ->limit($targetCount)
            ->get();

        $codes = [];

        foreach ($users as $user) {
            $codes[] = [
                'uuid' => RealisticSeederContent::stableUuid($user->id + 10000),
                'user_id' => $user->id,
                'channel' => 'email',
                'provider' => 'mailhog',
                'purpose' => 'account_deletion',
                'code' => 'magic',
                'meta' => json_encode([
                    'token_hash' => hash('sha256', \Illuminate\Support\Str::random(16)),
                    'reason' => RealisticSeederContent::accountDeletionReason($user->id),
                ]),
                'expires_at' => now()->addHours(24),
                'consumed_at' => null,
                'created_at' => now()->subHours(1 + ($user->id % 6)),
                'updated_at' => now()->subHours(1 + ($user->id % 6)),
            ];
        }

        $this->batchInsertCodes($codes);
        $this->command->info('    ✓ Created '.count($codes).' account deletion codes');

        return count($codes);
    }

    private function batchInsertCodes(array $codes): void
    {
        if (empty($codes)) {
            return;
        }

        foreach (array_chunk($codes, self::CHUNK_SIZE) as $chunk) {
            DB::table('otp_codes')->insertOrIgnore($chunk);
        }
    }
}
