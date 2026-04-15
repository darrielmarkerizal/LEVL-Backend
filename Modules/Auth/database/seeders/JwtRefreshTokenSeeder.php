<?php

declare(strict_types=1);

namespace Modules\Auth\Database\Seeders;

use App\Support\RealisticSeederContent;
use Illuminate\Database\Seeder;
use Modules\Auth\Models\User;

class JwtRefreshTokenSeeder extends Seeder
{
    public function run(): void
    {
        echo "Creating JWT refresh tokens...\n";

        $users = User::whereNull('deleted_at')->get();

        if ($users->isEmpty()) {
            echo "⚠️  No users found. Skipping JWT token seeding.\n";

            return;
        }

        $tokens = [];
        $count = 0;

        foreach ($users as $user) {
            $tokenCount = match ($user->status) {
                'active' => 2 + ($user->id % 4),
                'pending' => 0,
                'inactive' => 1,
                'banned' => 1,
                default => 0,
            };

            if ($tokenCount === 0) {
                continue;
            }

            for ($i = 0; $i < $tokenCount; $i++) {
                $salt = $user->id * 100 + $i;
                $tokens[] = [
                    'user_id' => $user->id,
                    'device_id' => hash('sha256', RealisticSeederContent::ipv4ForIndex($salt).RealisticSeederContent::userAgentForIndex($salt).$user->id),
                    'token' => hash('sha256', \Illuminate\Support\Str::random(64)),
                    'ip' => RealisticSeederContent::ipv4ForIndex($salt),
                    'user_agent' => RealisticSeederContent::userAgentForIndex($salt),
                    'last_used_at' => now()->subDays($salt % 8),
                    'idle_expires_at' => now()->addDays(14),
                    'absolute_expires_at' => now()->addDays(90),
                    'revoked_at' => in_array($user->status, ['inactive', 'banned'], true)
                        ? now()->subDays(1 + ($salt % 30))
                        : null,
                    'replaced_by' => null,
                    'created_at' => now()->subDays($salt % 31),
                    'updated_at' => now()->subDays($salt % 31),
                ];
                $count++;
            }
        }

        if (! empty($tokens)) {
            foreach (array_chunk($tokens, 1000) as $chunk) {
                \Illuminate\Support\Facades\DB::table('jwt_refresh_tokens')->insertOrIgnore($chunk);
            }
        }

        echo "✅ Created $count JWT refresh tokens\n";
    }
}
