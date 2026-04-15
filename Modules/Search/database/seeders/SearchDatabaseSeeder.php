<?php

declare(strict_types=1);

namespace Modules\Search\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;

class SearchDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding search history...');

        $userIds = User::query()->role('Student')->orderBy('id')->limit(40)->pluck('id')->all();
        if ($userIds === []) {
            return;
        }

        $queries = [
            'sertifikasi LSP',
            'UI UX design',
            'Laravel API',
            'quiz passing grade',
            'assignment deadline',
            'forum diskusi',
        ];

        $rows = [];
        $base = now()->subDays(14);
        foreach ($userIds as $i => $userId) {
            $q = $queries[$i % count($queries)];
            $rows[] = [
                'user_id' => $userId,
                'query' => $q,
                'filters' => json_encode(['scope' => 'courses']),
                'results_count' => random_int(3, 40),
                'clicked_result_id' => null,
                'clicked_result_type' => null,
                'created_at' => $base->copy()->addHours($i + 1)->toDateTimeString(),
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('search_history')->insert($chunk);
        }

        $this->command->info('Search history seeded: '.count($rows).' rows.');
    }
}
