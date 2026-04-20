<?php

declare(strict_types=1);

namespace Modules\Gamification\Database\Seeders;

use App\Support\SeederDate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Common\Models\LevelConfig;
use Modules\Gamification\Models\UserGamificationStat;

class ReconcileUserGamificationStatsSeeder extends Seeder
{
    public function run(): void
    {
        if (config('seeding.mode') !== 'uat') {
            return;
        }

        $this->command->info('UAT: reconciling user_gamification_stats from points sum...');

        $studentIds = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'Student')
            ->where('model_has_roles.model_type', 'Modules\\Auth\\Models\\User')
            ->pluck('users.id')
            ->all();

        $configs = LevelConfig::query()->orderBy('level')->get();

        foreach ($studentIds as $userId) {
            $totalXp = (int) DB::table('points')->where('user_id', $userId)->sum('points');
            $level = $this->resolveLevel($totalXp, $configs);

            UserGamificationStat::query()->updateOrCreate(
                ['user_id' => $userId],
                [
                    'total_xp' => $totalXp,
                    'global_level' => $level,
                    'current_streak' => 0,
                    'longest_streak' => 0,
                    'last_activity_date' => SeederDate::randomPastCarbonBetween(1, 180)->toDateString(),
                    'stats_updated_at' => SeederDate::randomPastDateTimeBetween(1, 180),
                ]
            );
        }

        $this->command->info('UAT: reconciled stats for '.count($studentIds).' students.');

        User::query()
            ->whereNotExists(function ($q): void {
                $q->selectRaw('1')
                    ->from('user_gamification_stats')
                    ->whereColumn('user_gamification_stats.user_id', 'users.id');
            })
            ->orderBy('id')
            ->chunk(200, function ($users): void {
                foreach ($users as $user) {
                    UserGamificationStat::query()->firstOrCreate(
                        ['user_id' => $user->id],
                        [
                            'total_xp' => 0,
                            'global_level' => 1,
                            'current_streak' => 0,
                            'longest_streak' => 0,
                            'last_activity_date' => SeederDate::randomPastCarbonBetween(1, 180)->toDateString(),
                            'stats_updated_at' => SeederDate::randomPastDateTimeBetween(1, 180),
                        ]
                    );
                }
            });
    }

    private function resolveLevel(int $totalXp, $configs): int
    {
        if ($configs->isEmpty()) {
            return 1;
        }

        $level = 1;
        foreach ($configs as $config) {
            if ($totalXp >= $config->xp_required) {
                $level = $config->level;
            } else {
                break;
            }
        }

        return $level;
    }
}
