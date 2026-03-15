<?php

declare(strict_types=1);

namespace Modules\Gamification\Services\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Gamification\Events\UserLeveledUp;
use Modules\Gamification\Models\Point;
use Modules\Gamification\Models\UserGamificationStat;
use Modules\Gamification\Models\UserScopeStat;
use Modules\Gamification\Models\XpDailyCap;
use Modules\Gamification\Models\XpSource;
use Modules\Gamification\Repositories\GamificationRepository;
use Modules\Gamification\Services\LevelService;
use Modules\Learning\Models\Assignment;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\Unit;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PointManager
{
    public function __construct(
        private readonly GamificationRepository $repository,
        private readonly LevelService $levelService
    ) {}

    public function awardXp(
        int $userId,
        int $points,
        string $reason,
        ?string $sourceType = null,
        ?int $sourceId = null,
        array $options = []
    ): ?Point {
        return DB::transaction(function () use ($userId, $points, $reason, $sourceType, $sourceId, $options) {
            try {
                // Check if XP already awarded for this specific source (prevent re-awarding after deletion)
                if ($sourceType && $sourceId) {
                    $existingPoint = Point::where('user_id', $userId)
                        ->where('reason', $reason)
                        ->where('source_type', $sourceType)
                        ->where('source_id', $sourceId)
                        ->first();
                    
                    if ($existingPoint) {
                        Log::info('XP award blocked: Already awarded for this source', [
                            'user_id' => $userId,
                            'reason' => $reason,
                            'source_type' => $sourceType,
                            'source_id' => $sourceId,
                        ]);
                        return null;
                    }
                }
                
                // Get XP source configuration if exists
                $xpSource = XpSource::byCode($reason)->active()->first();
                
                if ($xpSource) {
                    // Override points with configured amount
                    $points = $xpSource->xp_amount;
                    
                    // Check XP source specific limits
                    if (!$this->checkXpSourceLimits($userId, $xpSource)) {
                        Log::info('XP award blocked: XP source limit reached', [
                            'user_id' => $userId,
                            'xp_source' => $xpSource->code,
                        ]);
                        return null;
                    }
                }

                // Anti-Farming Checks
                $resolvedSourceType = $sourceType ?? 'system';

                if (! $this->checkCooldown($userId, $resolvedSourceType, $reason, $xpSource)) {
                    Log::info('XP award blocked: Cooldown active', [
                        'user_id' => $userId,
                        'reason' => $reason,
                    ]);
                    return null;
                }

                if (! $this->checkDailyCap($userId, $points, $resolvedSourceType, $xpSource)) {
                    Log::info('XP award blocked: Daily cap reached', [
                        'user_id' => $userId,
                        'reason' => $reason,
                    ]);
                    return null;
                }
                
                // Check Global Daily XP Cap
                if (! $this->checkGlobalDailyCap($userId, $points, $reason)) {
                    Log::warning('XP award blocked: Global daily cap reached', [
                        'user_id' => $userId,
                        'points' => $points,
                        'reason' => $reason,
                    ]);
                    return null;
                }

                // Get old level before awarding XP
                $stats = $this->repository->getOrCreateStats($userId);
                $oldLevel = $stats->global_level;

                // Create point transaction with enhanced logging
                $point = $this->repository->createPoint([
                    'user_id' => $userId,
                    'points' => $points,
                    'reason' => $reason,
                    'source_type' => $resolvedSourceType,
                    'source_id' => $sourceId,
                    'description' => $options['description'] ?? null,
                    'xp_source_code' => $xpSource?->code,
                    'old_level' => $oldLevel,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'metadata' => $options['metadata'] ?? null,
                ]);

                $updatedStats = $this->updateUserGamificationStats($userId, $points);
                $newLevel = $updatedStats->global_level;
                
                // Update point with new level
                $point->new_level = $newLevel;
                $point->triggered_level_up = $newLevel > $oldLevel;
                $point->save();
                
                // Update global daily cap tracking
                $this->updateGlobalDailyCap($userId, $points, $reason);
                
                // Check if user leveled up
                if ($newLevel > $oldLevel) {
                    $this->handleLevelUp($userId, $oldLevel, $newLevel, $updatedStats->total_xp);
                }
                
                $this->updateScopeStats($userId, $points, $sourceType, $sourceId);
                $this->checkAndIncrementStreak($userId);

                cache()->tags(['gamification', 'leaderboard'])->flush();

                return $point;
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                // Race condition handled: Point already exists
                Log::info('XP award blocked: Race condition (duplicate)', [
                    'user_id' => $userId,
                    'reason' => $reason,
                ]);
                return null;
            }
        });
    }

    private function updateScopeStats(int $userId, int $points, ?string $sourceType, ?int $sourceId): void
    {
        if (! $sourceType || ! $sourceId) {
            return;
        }

        $scopes = $this->resolveScopes($sourceType, $sourceId);

        foreach ($scopes as $type => $id) {
            if (! $id) {
                continue;
            }

            $stat = UserScopeStat::firstOrCreate(
                [
                    'user_id' => $userId,
                    'scope_type' => $type,
                    'scope_id' => $id,
                ],
                [
                    'total_xp' => 0,
                    'current_level' => 1,
                ]
            );

            $stat->total_xp += $points;
            $stat->current_level = $this->calculateLevelFromXp($stat->total_xp);
            $stat->save();
        }
    }

    private function resolveScopes(string $sourceType, int $sourceId): array
    {
        $scopes = [
            'course' => null,
            'unit' => null,
        ];

        try {
            switch ($sourceType) {
                case 'lesson':
                    $lesson = Lesson::find($sourceId);
                    if ($lesson) {
                        $scopes['unit'] = $lesson->unit_id;
                        $scopes['course'] = $lesson->unit?->course_id;
                    }
                    break;

                case 'assignment':
                    $assignment = Assignment::find($sourceId);
                    if ($assignment) {

                        if ($assignment->assignable_type === Course::class) {
                            $scopes['course'] = $assignment->assignable_id;
                        } elseif ($assignment->assignable_type === Unit::class) {
                            $scopes['unit'] = $assignment->assignable_id;
                            $scopes['course'] = $assignment->assignable?->course_id;
                        } elseif ($assignment->assignable_type === Lesson::class) {
                            $scopes['unit'] = $assignment->assignable?->unit_id;
                            $scopes['course'] = $assignment->assignable?->unit?->course_id;
                        } elseif ($assignment->lesson_id) {
                            $lesson = $assignment->lesson;
                            $scopes['unit'] = $lesson?->unit_id;
                            $scopes['course'] = $lesson?->unit?->course_id;
                        }
                    }
                    break;

                case 'attempt':

                    break;

                case 'course':
                    $scopes['course'] = $sourceId;
                    break;

                case 'grade':

                    $grade = \Modules\Grading\Models\Grade::find($sourceId);
                    if ($grade && $grade->source_type->value === 'assignment') {

                        $assignmentResults = $this->resolveScopes('assignment', (int) $grade->source_id);
                        $scopes['course'] = $assignmentResults['course'] ?? null;
                        $scopes['unit'] = $assignmentResults['unit'] ?? null;
                    }
                    break;
            }
        } catch (\Throwable $e) {

        }

        return $scopes;
    }

    private function updateUserGamificationStats(int $userId, int $points): UserGamificationStat
    {
        $stats = $this->repository->getOrCreateStats($userId);
        $stats->total_xp += $points;
        $stats->global_level = $this->calculateLevelFromXp($stats->total_xp);
        $stats->stats_updated_at = Carbon::now();
        $stats->last_activity_date = Carbon::now()->startOfDay();

        return $this->repository->saveStats($stats);
    }

    public function calculateLevelFromXp(int $totalXp): int
    {
        return $this->levelService->calculateLevelFromXp($totalXp);
    }

    public function getOrCreateStats(int $userId): UserGamificationStat
    {
        return $this->repository->getOrCreateStats($userId);
    }

    public function getPointsHistory(int $userId, int $perPage, $request = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));

        // Build cache key with all query parameters
        $cacheKey = "gamification:points:history:{$userId}:{$perPage}:"
            . request('page', 1) . ':'
            . request('filter.source_type', 'all') . ':'
            . request('filter.reason', 'all') . ':'
            . request('filter.period', 'all_time') . ':'
            . request('filter.month', 'all') . ':'
            . request('sort', '-created_at');

        return cache()->tags(['gamification', 'points'])->remember(
            $cacheKey,
            300,
            function () use ($userId, $perPage) {
                return QueryBuilder::for(Point::class)
                    ->where('user_id', $userId)
                    ->allowedFilters([
                        AllowedFilter::exact('source_type'),
                        AllowedFilter::exact('reason'),
                        AllowedFilter::callback('month', function ($query, $value) {
                            // Format: YYYY-MM (e.g., 2026-01, 2026-02)
                            if (preg_match('/^\d{4}-\d{2}$/', $value)) {
                                try {
                                    $date = Carbon::createFromFormat('Y-m', $value);
                                    $query->whereYear('points.created_at', $date->year)
                                        ->whereMonth('points.created_at', $date->month);
                                } catch (\Exception $e) {
                                    // Invalid date format, ignore filter
                                }
                            }
                        }),
                        AllowedFilter::callback('period', function ($query, $value) {
                            // Skip period filter if month filter is present
                            if (request()->has('filter.month')) {
                                return;
                            }
                            
                            $dateColumn = 'points.created_at';
                            match ($value) {
                                'today' => $query->whereDate($dateColumn, Carbon::today()),
                                'this_week' => $query->whereBetween($dateColumn, [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]),
                                'this_month' => $query->whereMonth($dateColumn, Carbon::now()->month)
                                    ->whereYear($dateColumn, Carbon::now()->year),
                                'this_year' => $query->whereYear($dateColumn, Carbon::now()->year),
                                default => null,
                            };
                        }),
                        AllowedFilter::callback('date_from', function ($query, $value) {
                            $query->whereDate('points.created_at', '>=', $value);
                        }),
                        AllowedFilter::callback('date_to', function ($query, $value) {
                            $query->whereDate('points.created_at', '<=', $value);
                        }),
                        AllowedFilter::callback('points_min', function ($query, $value) {
                            $query->where('points.points', '>=', (int) $value);
                        }),
                        AllowedFilter::callback('points_max', function ($query, $value) {
                            $query->where('points.points', '<=', (int) $value);
                        }),
                    ])
                    ->defaultSort('-created_at')
                    ->allowedSorts(['created_at', 'points', 'source_type', 'reason'])
                    ->paginate($perPage);
            }
        );
    }

    public function getAchievements(int $totalXp, int $currentLevel): array
    {
        $milestones = \Modules\Gamification\Models\Milestone::active()
            ->ordered()
            ->get();

        $achievements = $milestones->map(function ($milestone) use ($totalXp) {
            $achieved = $totalXp >= $milestone->xp_required;
            $progress = min(100, ($totalXp / $milestone->xp_required) * 100);

            return [
                'name' => $milestone->name,
                'xp_required' => $milestone->xp_required,
                'level_required' => $milestone->level_required,
                'achieved' => $achieved,
                'progress' => round($progress, 2),
            ];
        });

        $nextMilestone = $achievements->first(fn ($m) => ! $m['achieved']);

        return [
            'achievements' => $achievements,
            'next_milestone' => $nextMilestone,
            'current_xp' => $totalXp,
            'current_level' => $currentLevel,
        ];
    }

    private function checkCooldown(int $userId, string $sourceType, string $reason, ?XpSource $xpSource = null): bool
    {
        $cooldownSeconds = $xpSource?->cooldown_seconds ?? 0;
        
        // Use XP source cooldown if configured
        if ($cooldownSeconds > 0) {
            $lastPoint = Point::where('user_id', $userId)
                ->where('reason', $reason)
                ->latest()
                ->first();

            if ($lastPoint && $lastPoint->created_at->diffInSeconds(Carbon::now()) < $cooldownSeconds) {
                return false;
            }
            
            return true;
        }
        
        // Fallback to legacy cooldown logic
        // 10 Seconds Cooldown for 'lesson' completion to prevent rapid-fire API calls
        if ($sourceType === 'lesson' && $reason === 'completion') {
            $lastPoint = Point::where('user_id', $userId)
                ->where('source_type', $sourceType)
                ->where('reason', $reason)
                ->latest()
                ->first();

            if ($lastPoint && $lastPoint->created_at->diffInSeconds(Carbon::now()) < 10) {
                return false;
            }
        }

        return true;
    }

    private function checkDailyCap(int $userId, int $points, string $sourceType, ?XpSource $xpSource = null): bool
    {
        $dailyXpCap = $xpSource?->daily_xp_cap;
        
        // Use XP source daily cap if configured
        if ($dailyXpCap !== null && $dailyXpCap > 0) {
            $cacheKey = "gamification.daily_cap.{$userId}." . Carbon::today()->format('Y-m-d') . ".{$xpSource->code}";
            $currentDailyXp = \Illuminate\Support\Facades\Cache::get($cacheKey, 0);

            if ($currentDailyXp + $points > $dailyXpCap) {
                return false;
            }

            \Illuminate\Support\Facades\Cache::increment($cacheKey, $points);
            if ($currentDailyXp === 0) {
                \Illuminate\Support\Facades\Cache::put($cacheKey, $points, Carbon::tomorrow()->addHour());
            }
            
            return true;
        }
        
        // Fallback to legacy daily cap logic
        // Daily Cap for 'lesson' farming: Max 5000 XP per day
        if ($sourceType === 'lesson') {
            $todayScale = 'gamification.daily_cap.'.$userId.'.'.Carbon::today()->format('Y-m-d');
            $currentDailyXp = \Illuminate\Support\Facades\Cache::get($todayScale, 0);

            if ($currentDailyXp + $points > 5000) {
                return false;
            }

            \Illuminate\Support\Facades\Cache::increment($todayScale, $points);
            // Cache expires at end of day + buffer
            if ($currentDailyXp === 0) {
                \Illuminate\Support\Facades\Cache::put($todayScale, $points, Carbon::tomorrow()->addHour());
            }
        }

        return true;
    }
    
    private function checkXpSourceLimits(int $userId, XpSource $xpSource): bool
    {
        // Check daily limit (max times per day)
        if ($xpSource->daily_limit !== null && $xpSource->daily_limit > 0) {
            $cacheKey = "gamification.daily_limit.{$userId}." . Carbon::today()->format('Y-m-d') . ".{$xpSource->code}";
            $currentCount = \Illuminate\Support\Facades\Cache::get($cacheKey, 0);

            if ($currentCount >= $xpSource->daily_limit) {
                return false;
            }

            \Illuminate\Support\Facades\Cache::increment($cacheKey);
            if ($currentCount === 0) {
                \Illuminate\Support\Facades\Cache::put($cacheKey, 1, Carbon::tomorrow()->addHour());
            }
        }

        return true;
    }
    
    private function handleLevelUp(int $userId, int $oldLevel, int $newLevel, int $totalXp): void
    {
        // Get rewards for the new level
        $levelConfig = $this->levelService->getLevelConfig($newLevel);
        $rewards = $levelConfig?->rewards ?? [];
        $bonusXp = $levelConfig?->bonus_xp ?? 0;
        
        // Award bonus XP for leveling up
        if ($bonusXp > 0) {
            $this->repository->createPoint([
                'user_id' => $userId,
                'points' => $bonusXp,
                'reason' => 'bonus',
                'source_type' => 'system',
                'source_id' => null,
                'description' => sprintf('Bonus XP untuk mencapai level %d', $newLevel),
                'xp_source_code' => null,
                'old_level' => $newLevel,
                'new_level' => $newLevel,
                'triggered_level_up' => false,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'level_up' => true,
                    'old_level' => $oldLevel,
                    'new_level' => $newLevel,
                ],
            ]);
            
            // Update user stats with bonus XP (without triggering another level up check)
            $stats = $this->repository->getOrCreateStats($userId);
            $stats->total_xp += $bonusXp;
            $this->repository->saveStats($stats);
        }
        
        // Dispatch level up event
        event(new UserLeveledUp(
            userId: $userId,
            oldLevel: $oldLevel,
            newLevel: $newLevel,
            totalXp: $totalXp + $bonusXp,
            rewards: array_merge($rewards, ['bonus_xp' => $bonusXp])
        ));
    }
    
    private function checkGlobalDailyCap(int $userId, int $points, string $reason): bool
    {
        $today = Carbon::today();
        
        // Get or create daily cap record
        $dailyCap = XpDailyCap::firstOrCreate(
            [
                'user_id' => $userId,
                'date' => $today,
            ],
            [
                'total_xp_earned' => 0,
                'global_daily_cap' => config('gamification.global_daily_xp_cap', 10000),
                'xp_by_source' => [],
            ]
        );
        
        // Check if adding this XP would exceed the cap
        if ($dailyCap->total_xp_earned + $points > $dailyCap->global_daily_cap) {
            return false;
        }
        
        return true;
    }
    
    private function updateGlobalDailyCap(int $userId, int $points, string $reason): void
    {
        $today = Carbon::today();
        
        $dailyCap = XpDailyCap::firstOrCreate(
            [
                'user_id' => $userId,
                'date' => $today,
            ],
            [
                'total_xp_earned' => 0,
                'global_daily_cap' => config('gamification.global_daily_xp_cap', 10000),
                'xp_by_source' => [],
            ]
        );
        
        $dailyCap->incrementXp($points, $reason);
    }
    
    public function getDailyXpStats(int $userId): array
    {
        $today = Carbon::today();
        
        $dailyCap = XpDailyCap::where('user_id', $userId)
            ->where('date', $today)
            ->first();
        
        if (!$dailyCap) {
            return [
                'total_xp_earned' => 0,
                'global_daily_cap' => config('gamification.global_daily_xp_cap', 10000),
                'remaining_xp' => config('gamification.global_daily_xp_cap', 10000),
                'cap_reached' => false,
                'xp_by_source' => [],
            ];
        }
        
        return [
            'total_xp_earned' => $dailyCap->total_xp_earned,
            'global_daily_cap' => $dailyCap->global_daily_cap,
            'remaining_xp' => $dailyCap->getRemainingXp(),
            'cap_reached' => $dailyCap->cap_reached,
            'cap_reached_at' => $dailyCap->cap_reached_at,
            'xp_by_source' => $dailyCap->xp_by_source ?? [],
        ];
    }

    private function checkAndIncrementStreak(int $userId): void
    {
        $stats = $this->repository->getOrCreateStats($userId);
        $lastActivityDate = $stats->last_activity_date ? Carbon::parse($stats->last_activity_date) : null;
        $today = Carbon::today();

        if (! $lastActivityDate || ! $lastActivityDate->isToday()) {
            $stats->current_streak++;
            $stats->longest_streak = max($stats->longest_streak, $stats->current_streak);
        }

        $stats->last_activity_date = $today;
        $this->repository->saveStats($stats);
    }
}
