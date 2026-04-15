<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UATSeederIntegrityCheckSeeder extends Seeder
{
    public function run(): void
    {
        if (config('seeding.mode') !== 'uat') {
            return;
        }

        $this->command->info('UAT integrity: comparing points sum vs user_gamification_stats...');

        $mismatches = DB::select('
            SELECT ugs.user_id, ugs.total_xp AS stat_xp, COALESCE(SUM(p.points), 0)::bigint AS ledger_sum
            FROM user_gamification_stats ugs
            LEFT JOIN points p ON p.user_id = ugs.user_id
            GROUP BY ugs.user_id, ugs.total_xp
            HAVING ugs.total_xp <> COALESCE(SUM(p.points), 0)::bigint
            LIMIT 50
        ');

        if ($mismatches !== []) {
            $this->command->warn('UAT integrity: found '.count($mismatches).' user(s) with total_xp != sum(points).');
            foreach (array_slice($mismatches, 0, 10) as $row) {
                $this->command->warn("  user_id={$row->user_id} stat_xp={$row->stat_xp} ledger_sum={$row->ledger_sum}");
            }
        } else {
            $this->command->info('UAT integrity: all sampled stats match point ledger.');
        }

        $orphanPoints = DB::table('points as p')
            ->leftJoin('users as u', 'p.user_id', '=', 'u.id')
            ->whereNull('u.id')
            ->count();

        if ($orphanPoints > 0) {
            $this->command->warn("UAT integrity: {$orphanPoints} point row(s) reference missing users.");
        }

        $activityRows = DB::table('enrollment_activities')->count();
        $this->command->info("UAT integrity: enrollment_activities rows = {$activityRows}.");
    }
}
