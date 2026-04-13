<?php

declare(strict_types=1);

namespace Modules\Gamification\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as PaginationLengthAwarePaginator;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Auth\Models\User;
use Modules\Gamification\Contracts\Services\GamificationServiceInterface;
use Modules\Gamification\Contracts\Services\LeaderboardServiceInterface;
use Modules\Gamification\Models\Point;
use Modules\Gamification\Models\UserBadge;
use Modules\Gamification\Models\UserGamificationStat;
use Modules\Gamification\Repositories\GamificationRepository;
use Modules\Gamification\Services\Support\BadgeManager;
use Modules\Gamification\Services\Support\LeaderboardManager;
use Modules\Gamification\Services\Support\PointManager;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;

class GamificationService implements GamificationServiceInterface
{
    private readonly GamificationRepository $repository;

    public function __construct(
        private readonly PointManager $pointManager,
        private readonly BadgeManager $badgeManager,
        private readonly LeaderboardManager $leaderboardManager,
        private readonly LeaderboardServiceInterface $leaderboardService,
        ?GamificationRepository $repository = null
    ) {
        $this->repository = $repository ?? app(GamificationRepository::class);
    }

    public function render(string $template, array $data = []): View
    {
        return view($this->repository->view($template), $data);
    }

    public function awardXp(
        int $userId,
        int $points,
        string $reason,
        ?string $sourceType = null,
        ?int $sourceId = null,
        array $options = []
    ): ?Point {
        return $this->pointManager->awardXp($userId, $points, $reason, $sourceType, $sourceId, $options);
    }

    public function awardBadge(
        int $userId,
        string $code,
        string $name,
        ?string $description = null
    ): ?UserBadge {
        return $this->badgeManager->awardBadge($userId, $code, $name, $description);
    }

    public function updateGlobalLeaderboard(): void
    {
        $this->leaderboardManager->updateGlobalLeaderboard();
    }

    public function getOrCreateStats(int $userId): UserGamificationStat
    {
        return $this->pointManager->getOrCreateStats($userId);
    }

    public function getUserBadges(int $userId, int $perPage = 15, $request = null): LengthAwarePaginator
    {
        return $this->badgeManager->getUserBadgesPaginated($userId, $perPage, $request);
    }

    public function getUserBadgesCollection(int $userId): Collection
    {
        return $this->badgeManager->getUserBadges($userId);
    }

    public function countUserBadges(int $userId): int
    {
        return $this->badgeManager->countUserBadges($userId);
    }

    public function getPointsHistory(int $userId, int $perPage, $request = null): LengthAwarePaginator
    {
        return $this->pointManager->getPointsHistory($userId, $perPage, $request);
    }

    public function getUserGamificationLog(int $userId, int $perPage = 15, $request = null): array
    {
        $request = $request instanceof Request ? $request : request();
        $perPage = max(1, min($perPage, 100));
        $items = $this->buildGamificationLogItems($userId, $request);
        $page = max(1, (int) $request->input('page', 1));

        $logs = new PaginationLengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return [
            'summary' => $this->buildGamificationLogSummary($userId),
            'logs' => $logs,
        ];
    }

    public function getUserGamificationLogExportRows(int $userId, $request = null): array
    {
        $request = $request instanceof Request ? $request : request();

        return $this->buildGamificationLogItems($userId, $request)
            ->map(fn (array $item) => [
                'date_time' => $this->formatExportDateTime($item['created_at']),
                'description' => $item['description'],
                'reward' => $item['reward_text'],
                'category' => '['.$item['category_label'].']',
                'event_type' => $item['event_type'],
            ])
            ->values()
            ->all();
    }

    public function exportUserGamificationLog(int $userId, string $type = 'csv', $request = null): Response
    {
        $request = $request instanceof Request ? $request : request();
        $exportType = strtolower($type);

        if (! in_array($exportType, ['csv', 'excel', 'pdf', 'json'], true)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.validation_failed'),
                'errors' => ['type' => ['Supported export types: csv, excel, pdf, json']],
            ], 422);
        }

        $rows = $this->getUserGamificationLogExportRows($userId, $request);
        $baseFileName = sprintf('gamification-log-user-%d-%s', $userId, now()->format('Ymd-His'));

        if ($exportType === 'json') {
            return response()->streamDownload(function () use ($rows) {
                echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }, $baseFileName.'.json', ['Content-Type' => 'application/json']);
        }

        if ($exportType === 'excel') {
            $export = new class($rows) implements FromArray, WithHeadings
            {
                public function __construct(
                    private readonly array $rows
                ) {}

                public function headings(): array
                {
                    return ['Date Time', 'Description', 'Reward', 'Category', 'Event Type'];
                }

                public function array(): array
                {
                    return array_map(
                        fn (array $row): array => [
                            $row['date_time'],
                            $row['description'],
                            $row['reward'],
                            $row['category'],
                            $row['event_type'],
                        ],
                        $this->rows
                    );
                }
            };

            return response()->streamDownload(function () use ($export) {
                echo Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
            }, $baseFileName.'.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        if ($exportType === 'pdf') {
            $options = new Options;
            $options->set('isRemoteEnabled', false);
            $dompdf = new Dompdf($options);

            $html = '<html><head><style>body{font-family: DejaVu Sans, sans-serif;font-size:10px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #d0d7de;padding:6px;text-align:left;}th{background:#f3f4f6;}</style></head><body><h3>Gamification Log</h3><table><thead><tr><th>Date Time</th><th>Description</th><th>Reward</th><th>Category</th><th>Event Type</th></tr></thead><tbody>';

            foreach ($rows as $row) {
                $html .= '<tr>'
                    .'<td>'.e((string) $row['date_time']).'</td>'
                    .'<td>'.e((string) $row['description']).'</td>'
                    .'<td>'.e((string) $row['reward']).'</td>'
                    .'<td>'.e((string) $row['category']).'</td>'
                    .'<td>'.e((string) $row['event_type']).'</td>'
                    .'</tr>';
            }

            $html .= '</tbody></table></body></html>';

            $dompdf->loadHtml($html);
            $dompdf->setPaper('a4', 'landscape');
            $dompdf->render();

            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$baseFileName.'.pdf"',
            ]);
        }

        // CSV export (default)
        // Bersihkan semua output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date Time', 'Description', 'Reward', 'Category', 'Event Type']);
            foreach ($rows as $row) {
                fputcsv($handle, [$row['date_time'], $row['description'], $row['reward'], $row['category'], $row['event_type']]);
            }
            fclose($handle);
        }, $baseFileName.'.csv', ['Content-Type' => 'text/csv']);
    }

    public function getAchievements(int $userId): array
    {
        $stats = $this->pointManager->getOrCreateStats($userId);

        return $this->pointManager->getAchievements($stats->total_xp, $stats->global_level);
    }

    public function getSummary(int $userId, string $period = 'all_time', ?string $month = null): array
    {
        $stats = $this->pointManager->getOrCreateStats($userId);
        $rankData = $this->leaderboardService->getUserRank($userId, $period, $month);

        // Get XP for the specified period/month
        $periodXp = $this->getPeriodXp($userId, $period, $month);

        // Get badges count for the specified period/month
        $badgesCount = $this->getBadgesCountForPeriod($userId, $period, $month);

        return [
            'xp' => [
                'total' => $stats->total_xp,
                'today' => $this->getPeriodXp($userId, 'today'),
                'this_week' => $this->getPeriodXp($userId, 'this_week'),
                'this_month' => $this->getPeriodXp($userId, 'this_month'),
                'period' => $periodXp, // XP for requested period/month
            ],
            'level' => [
                'current' => $stats->global_level,
                'name' => $this->getLevelName($stats->global_level),
                'progress_percentage' => $stats->progress_to_next_level,
                'xp_to_next_level' => $stats->xp_to_next_level,
            ],
            'badges' => [
                'total_earned' => $this->badgeManager->countUserBadges($userId),
                'period_earned' => $badgesCount, // Badges earned in requested period/month
            ],
            'leaderboard' => [
                'global_rank' => $rankData['rank'],
                'total_students' => $this->getTotalStudents(),
            ],
            'activity' => [
                'current_streak' => $stats->current_streak,
                'longest_streak' => $stats->longest_streak,
            ],
        ];
    }

    private function getPeriodXp(int $userId, string $period, ?string $month = null): int
    {
        $query = \Modules\Gamification\Models\Point::where('user_id', $userId);

        // If month filter is present, use it
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            try {
                $date = \Carbon\Carbon::createFromFormat('Y-m', $month);
                $query->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month);
            } catch (\Exception $e) {
                // Invalid date, fallback to period
                $this->applyPeriodFilterToQuery($query, $period);
            }
        } else {
            $this->applyPeriodFilterToQuery($query, $period);
        }

        return (int) $query->sum('points');
    }

    private function applyPeriodFilterToQuery($query, string $period): void
    {
        $dateColumn = 'created_at';

        match ($period) {
            'today' => $query->whereDate($dateColumn, \Carbon\Carbon::today()),
            'this_week' => $query->whereBetween($dateColumn, [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]),
            'this_month' => $query->whereMonth($dateColumn, \Carbon\Carbon::now()->month)
                ->whereYear($dateColumn, \Carbon\Carbon::now()->year),
            'this_year' => $query->whereYear($dateColumn, \Carbon\Carbon::now()->year),
            default => null,
        };
    }

    private function getBadgesCountForPeriod(int $userId, string $period, ?string $month = null): int
    {
        $query = \Modules\Gamification\Models\UserBadge::where('user_id', $userId);

        // If month filter is present, use it
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            try {
                $date = \Carbon\Carbon::createFromFormat('Y-m', $month);
                $query->whereYear('earned_at', $date->year)
                    ->whereMonth('earned_at', $date->month);
            } catch (\Exception $e) {
                // Invalid date, fallback to period
                $this->applyPeriodFilterToBadges($query, $period);
            }
        } else {
            $this->applyPeriodFilterToBadges($query, $period);
        }

        return $query->count();
    }

    private function applyPeriodFilterToBadges($query, string $period): void
    {
        match ($period) {
            'today' => $query->whereDate('earned_at', \Carbon\Carbon::today()),
            'this_week' => $query->whereBetween('earned_at', [\Carbon\Carbon::now()->startOfWeek(), \Carbon\Carbon::now()->endOfWeek()]),
            'this_month' => $query->whereMonth('earned_at', \Carbon\Carbon::now()->month)
                ->whereYear('earned_at', \Carbon\Carbon::now()->year),
            'this_year' => $query->whereYear('earned_at', \Carbon\Carbon::now()->year),
            default => null,
        };
    }

    private function getLevelName(int $level): string
    {
        $levelConfig = \Modules\Common\Models\LevelConfig::where('level', $level)->first();

        return $levelConfig?->name ?? 'Level '.$level;
    }

    private function getTotalStudents(): int
    {
        return \Modules\Auth\Models\User::role('Student')->count();
    }

    private function buildGamificationLogSummary(int $userId): array
    {
        $user = User::query()
            ->select(['id', 'name', 'email', 'username'])
            ->find($userId);

        $stats = $this->pointManager->getOrCreateStats($userId);

        return [
            'participant' => [
                'id' => $user?->id,
                'name' => $user?->name,
                'email' => $user?->email,
                'username' => $user?->username,
                'avatar_url' => $user?->avatar_url,
            ],
            'current_stat' => 'Level '.$stats->global_level,
            'current_level' => $stats->global_level,
            'total_xp' => $stats->total_xp,
            'badges_count' => UserBadge::query()->where('user_id', $userId)->count(),
        ];
    }

    private function buildGamificationLogItems(int $userId, Request $request): Collection
    {
        $eventType = strtolower((string) $request->input('filter.event_type', 'all'));
        $events = collect();

        if ($eventType !== 'badge') {
            $events = $events->concat($this->buildPointLogItems($userId));
        }

        if ($eventType !== 'xp') {
            $events = $events->concat($this->buildBadgeLogItems($userId));
        }

        $sort = strtolower((string) $request->input('sort', '-created_at'));
        $isAscending = in_array($sort, ['created_at', 'oldest', 'oldest_first'], true);

        return $isAscending
            ? $events->sortBy('created_at')->values()
            : $events->sortByDesc('created_at')->values();
    }

    private function buildPointLogItems(int $userId): Collection
    {
        $points = QueryBuilder::for(Point::query()->where('user_id', $userId))
            ->allowedFilters([
                AllowedFilter::exact('source_type'),
                AllowedFilter::exact('reason'),
                AllowedFilter::callback('search', function ($query, $value): void {
                    $needle = '%'.strtolower((string) $value).'%';
                    $query->whereRaw("LOWER(COALESCE(description, '')) LIKE ?", [$needle]);
                }),
                AllowedFilter::callback('category', function ($query, $value): void {
                    $this->applyPointCategoryFilter($query, $this->normalizeCategory($value));
                }),
                AllowedFilter::callback('month', function ($query, $value): void {
                    $month = (string) $value;
                    if (preg_match('/^\d{4}-\d{2}$/', $month) !== 1) {
                        return;
                    }

                    try {
                        $date = \Carbon\Carbon::createFromFormat('Y-m', $month);
                        $query->whereYear('created_at', $date->year)
                            ->whereMonth('created_at', $date->month);
                    } catch (\Exception $e) {
                    }
                }),
                AllowedFilter::callback('period', function ($query, $value): void {
                    if (request()->has('filter.month')) {
                        return;
                    }

                    $this->applyPeriodFilterToQuery($query, (string) $value);
                }),
                AllowedFilter::callback('date_from', function ($query, $value): void {
                    $query->whereDate('created_at', '>=', (string) $value);
                }),
                AllowedFilter::callback('date_to', function ($query, $value): void {
                    $query->whereDate('created_at', '<=', (string) $value);
                }),
                AllowedFilter::callback('points_min', function ($query, $value): void {
                    $query->where('points', '>=', (int) $value);
                }),
                AllowedFilter::callback('points_max', function ($query, $value): void {
                    $query->where('points', '<=', (int) $value);
                }),
            ])
            ->defaultSort('-created_at')
            ->get();

        return $points->map(function (Point $point): array {
            $reason = $point->reason?->value;
            $category = $this->mapPointReasonToCategory($reason);

            return [
                'id' => 'xp-'.$point->id,
                'event_type' => 'xp',
                'description' => $point->description ?? ($point->reason?->label() ?? 'XP reward'),
                'reward_text' => $this->formatXpReward((int) $point->points),
                'reward_value' => (int) $point->points,
                'category' => $category,
                'category_label' => ucfirst($category),
                'created_at' => $point->created_at?->toISOString(),
            ];
        });
    }

    private function buildBadgeLogItems(int $userId): Collection
    {
        $badges = QueryBuilder::for(UserBadge::query()->where('user_id', $userId)->with(['badge:id,name,type']))
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value): void {
                    $needle = '%'.strtolower((string) $value).'%';
                    $query->whereHas('badge', function ($badgeQuery) use ($needle): void {
                        $badgeQuery->whereRaw("LOWER(COALESCE(name, '')) LIKE ?", [$needle])
                            ->orWhereRaw("LOWER(COALESCE(description, '')) LIKE ?", [$needle]);
                    });
                }),
                AllowedFilter::callback('category', function ($query, $value): void {
                    $category = $this->normalizeCategory($value);
                    if ($category === null) {
                        return;
                    }

                    $query->whereHas('badge', function ($badgeQuery) use ($category): void {
                        $badgeQuery->where('type', $category);
                    });
                }),
                AllowedFilter::callback('month', function ($query, $value): void {
                    $month = (string) $value;
                    if (preg_match('/^\d{4}-\d{2}$/', $month) !== 1) {
                        return;
                    }

                    try {
                        $date = \Carbon\Carbon::createFromFormat('Y-m', $month);
                        $query->whereYear('earned_at', $date->year)
                            ->whereMonth('earned_at', $date->month);
                    } catch (\Exception $e) {
                    }
                }),
                AllowedFilter::callback('period', function ($query, $value): void {
                    if (request()->has('filter.month')) {
                        return;
                    }

                    $this->applyPeriodFilterToBadges($query, (string) $value);
                }),
                AllowedFilter::callback('date_from', function ($query, $value): void {
                    $query->whereDate('earned_at', '>=', (string) $value);
                }),
                AllowedFilter::callback('date_to', function ($query, $value): void {
                    $query->whereDate('earned_at', '<=', (string) $value);
                }),
            ])
            ->defaultSort('-earned_at')
            ->get();

        return $badges->map(function (UserBadge $userBadge): array {
            $category = strtolower((string) ($userBadge->badge?->type?->value ?? 'completion'));

            return [
                'id' => 'badge-'.$userBadge->id,
                'event_type' => 'badge',
                'description' => 'System Award: '.($userBadge->badge?->name ?? 'Badge'),
                'reward_text' => '+1 Badge',
                'reward_value' => 1,
                'category' => $category,
                'category_label' => ucfirst($category),
                'created_at' => $userBadge->earned_at?->toISOString(),
            ];
        });
    }

    private function normalizeCategory(mixed $value): ?string
    {
        $category = strtolower(trim((string) $value));

        if ($category === '' || $category === 'all' || $category === 'all category') {
            return null;
        }

        return $category;
    }

    private function mapPointReasonToCategory(?string $reason): string
    {
        return match ($reason) {
            'perfect_score', 'quiz_passed', 'quiz_completed', 'score' => 'quality',
            'daily_streak' => 'habit',
            'forum_post', 'forum_reply', 'reaction_received', 'engagement' => 'social',
            'first_attempt', 'first_submission' => 'speed',
            'bonus', 'penalty' => 'hidden',
            default => 'completion',
        };
    }

    private function applyPointCategoryFilter($query, ?string $category): void
    {
        if ($category === null) {
            return;
        }

        match ($category) {
            'quality' => $query->whereIn('reason', ['perfect_score', 'quiz_passed', 'quiz_completed', 'score']),
            'habit' => $query->where('reason', 'daily_streak'),
            'social' => $query->whereIn('reason', ['forum_post', 'forum_reply', 'reaction_received', 'engagement']),
            'speed' => $query->whereIn('reason', ['first_attempt', 'first_submission']),
            'hidden' => $query->whereIn('reason', ['bonus', 'penalty']),
            'completion' => $query->whereIn('reason', ['lesson_completed', 'assignment_submitted', 'unit_completed', 'assignment_completed', 'completion']),
            default => $query->whereRaw('1 = 0'),
        };
    }

    private function formatXpReward(int $points): string
    {
        $prefix = $points >= 0 ? '+' : '-';

        return $prefix.number_format(abs($points)).' XP';
    }

    private function formatExportDateTime(?string $isoDateTime): string
    {
        if (! $isoDateTime) {
            return '';
        }

        return \Carbon\Carbon::parse($isoDateTime)->timezone('Asia/Jakarta')->format('M d, Y H:i').' WIB';
    }

    public function getUnitLevels(int $userId, int $courseId): Collection
    {
        $units = \Modules\Schemes\Models\Unit::where('course_id', $courseId)
            ->orderBy('order')
            ->get(['id', 'title', 'order']);

        $stats = \Modules\Gamification\Models\UserScopeStat::where('user_id', $userId)
            ->where('scope_type', 'unit')
            ->whereIn('scope_id', $units->pluck('id'))
            ->get()
            ->keyBy('scope_id');

        return $units->map(function ($unit) use ($stats) {
            $stat = $stats->get($unit->id);

            return [
                'unit_id' => $unit->id,
                'title' => $unit->title,
                'level' => $stat?->current_level ?? 1,
                'total_xp' => $stat?->total_xp ?? 0,
                'progress' => 0,
            ];
        });
    }
}
