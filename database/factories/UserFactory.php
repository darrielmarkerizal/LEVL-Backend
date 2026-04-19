<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Support\RealisticSeederContent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Auth\Enums\UserStatus;
use Modules\Auth\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    private static int $sequence = 0;

    public function definition(): array
    {
        self::$sequence++;
        $idx = self::$sequence;
        [$first, $last] = RealisticSeederContent::indonesianNamePair($idx);
        $fullName = $first.' '.$last;
        $username = Str::slug($first.'-'.$last.'-'.$idx, '_');
        $email = 'peserta.'.$idx.'@peserta.'.RealisticSeederContent::EMAIL_DOMAIN_DEMO;

        $statusCycle = [UserStatus::Active, UserStatus::Pending, UserStatus::Inactive];
        $status = $statusCycle[$idx % 3]->value;

        return [
            'name' => $fullName,
            'username' => $username,
            'email' => $email,
            'phone' => RealisticSeederContent::phoneForIndex($idx),
            'bio' => ($idx % 5 !== 0) ? RealisticSeederContent::bioForUser($idx) : null,
            'specialization_id' => null,
            'password' => static::$password ??= Hash::make('password'),
            'status' => $status,
            'email_verified_at' => ($idx % 5 !== 1) ? now()->subDays(($idx % 300) + 1) : null,
            'is_password_set' => true,
            'last_profile_update' => ($idx % 4 !== 0) ? now()->subDays(($idx % 60) + 1) : null,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'status' => UserStatus::Pending->value,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Pending->value,
            'email_verified_at' => null,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Active->value,
            'email_verified_at' => now()->subDays(30),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Inactive->value,
            'email_verified_at' => now()->subDays(90),
        ]);
    }

    public function banned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Banned->value,
            'email_verified_at' => now()->subDays(60),
        ]);
    }

    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Inactive->value,
        ])->trashed();
    }

    public function passwordNotSet(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_password_set' => false,
            'password' => Hash::make(Str::random(32)),
        ]);
    }

    public function withCompleteProfile(): static
    {
        return $this->state(function (array $attributes) {
            $email = $attributes['email'] ?? '';
            $idx = 1;
            if (preg_match('/peserta\.(\d+)@/', $email, $m)) {
                $idx = (int) $m[1];
            } else {
                $idx = (abs(crc32($email)) % 9999) + 1;
            }

            return [
                'phone' => RealisticSeederContent::phoneForIndex($idx),
                'bio' => RealisticSeederContent::bioForUser($idx),
                'last_profile_update' => now()->subDays(($idx % 20) + 1),
            ];
        });
    }

    public function withMinimalProfile(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone' => null,
            'bio' => null,
            'last_profile_update' => null,
        ]);
    }

    public function instructor(): static
    {
        return $this->state(function (array $attributes) {
            $categoryIds = \Modules\Common\Models\Category::where('status', 'active')
                ->pluck('id')
                ->toArray();

            $email = $attributes['email'] ?? '';
            $idx = 1;
            if (preg_match('/peserta\.(\d+)@/', $email, $m)) {
                $idx = (int) $m[1];
            } else {
                $idx = (abs(crc32($email)) % 9999) + 1;
            }

            $specializationId = ! empty($categoryIds)
                ? $categoryIds[$idx % count($categoryIds)]
                : null;

            return [
                'specialization_id' => $specializationId,
                'status' => UserStatus::Active->value,
                'email_verified_at' => now()->subDays(($idx % 200) + 1),
            ];
        });
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            if (! $user->trashed()) {
                $statsData = [
                    'total_points' => 0,
                    'current_streak' => 0,
                    'longest_streak' => 0,
                    'total_badges' => 0,
                ];

                if (Schema::hasColumn('user_gamification_stats', 'completed_challenges')) {
                    $statsData['completed_challenges'] = 0;
                }

                $user->gamificationStats()->firstOrCreate(
                    ['user_id' => $user->id],
                    $statsData
                );
            }
        });
    }
}
