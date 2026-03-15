<?php

declare(strict_types=1);

namespace Modules\Dashboard\Services;

use Illuminate\Support\Collection;
use Modules\Auth\Models\User;
use Modules\Enrollments\Models\Enrollment;
use Modules\Gamification\Models\UserBadge;
use Modules\Gamification\Models\UserGamificationStat;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Lesson;
use Spatie\QueryBuilder\QueryBuilder;

class DashboardService
{
    /**
     * Get dashboard overview
     */
    public function getOverview(int $userId): array
    {
        $stats = UserGamificationStat::where('user_id', $userId)->first();
        $levelConfig = \Modules\Common\Models\LevelConfig::where('level', $stats?->global_level ?? 1)->first();

        return [
            'streak' => [
                'current' => $stats?->current_streak ?? 0,
                'longest' => $stats?->longest_streak ?? 0,
            ],
            'level' => [
                'current' => $stats?->global_level ?? 1,
                'name' => $levelConfig?->name ?? 'Newbie',
                'current_xp' => $stats?->total_xp ?? 0,
                'required_xp' => $levelConfig?->xp_required ?? 0,
                'next_level_xp' => $this->getNextLevelXp($stats?->global_level ?? 1),
                'progress_percentage' => $this->calculateLevelProgress($stats),
            ],
            'xp' => [
                'total' => $stats?->total_xp ?? 0,
                'this_month' => $this->getXpThisMonth($userId),
            ],
        ];
    }

    /**
     * Get recent learning activities
     */
    public function getRecentLearning(int $userId, int $limit = 1): Collection
    {
        $enrollments = Enrollment::where('user_id', $userId)
            ->where('status', 'active')
            ->with([
                'course:id,title,slug',
                'course.media',
                'course.units' => function ($query) {
                    $query->select('id', 'course_id', 'title', 'order')
                        ->orderBy('order');
                },
                'course.units.lessons' => function ($query) {
                    $query->select('id', 'unit_id', 'title', 'order')
                        ->orderBy('order');
                },
            ])
            ->get();

        // Get lesson completions for sorting
        $lessonCompletions = \Modules\Schemes\Models\LessonCompletion::where('user_id', $userId)
            ->get()
            ->groupBy('lesson_id')
            ->map(fn($group) => $group->first());

        // Sort enrollments by most recent lesson completion or enrolled_at
        $enrollments = $enrollments->sortByDesc(function ($enrollment) use ($lessonCompletions, $userId) {
            $courseId = $enrollment->course_id;
            $lessonIds = $enrollment->course->units->flatMap(fn($unit) => $unit->lessons->pluck('id'));
            
            // Find most recent lesson completion for this course
            $mostRecentCompletion = $lessonCompletions
                ->filter(fn($completion) => $lessonIds->contains($completion->lesson_id))
                ->sortByDesc('updated_at')
                ->first();
            
            // Return most recent completion time or enrolled_at
            return $mostRecentCompletion ? $mostRecentCompletion->updated_at : $enrollment->enrolled_at;
        })->take($limit);

        return $enrollments->map(function ($enrollment) use ($userId) {
            $course = $enrollment->course;
            
            // Get total lessons
            $totalLessons = $course->units->sum(fn($unit) => $unit->lessons->count());
            
            // Get completed lessons
            $completedLessons = \Modules\Schemes\Models\LessonCompletion::where('user_id', $userId)
                ->whereIn('lesson_id', $course->units->flatMap(fn($unit) => $unit->lessons->pluck('id')))
                ->count();
            
            // Get last accessed lesson
            $lastLesson = \Modules\Schemes\Models\LessonCompletion::where('user_id', $userId)
                ->whereIn('lesson_id', $course->units->flatMap(fn($unit) => $unit->lessons->pluck('id')))
                ->latest('updated_at')
                ->first();
            
            $lastLessonData = null;
            if ($lastLesson) {
                $lesson = Lesson::find($lastLesson->lesson_id);
                $lastLessonData = [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'unit_title' => $lesson->unit->title ?? null,
                ];
            }
            
            $percentage = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0;

            return [
                'course' => [
                    'id' => $course->id,
                    'title' => $course->title,
                    'slug' => $course->slug,
                    'thumbnail' => $course->getFirstMediaUrl('thumbnail'),
                ],
                'progress' => [
                    'completed_lessons' => $completedLessons,
                    'total_lessons' => $totalLessons,
                    'percentage' => $percentage,
                ],
                'last_lesson' => $lastLessonData,
                'last_accessed_at' => $lastLesson ? $lastLesson->updated_at : $enrollment->updated_at,
            ];
        })->values();
    }

    /**
     * Get recent achievements (badges)
     */
    public function getRecentAchievements(int $userId, int $limit = 4): Collection
    {
        return QueryBuilder::for(UserBadge::class)
            ->where('user_id', $userId)
            ->with(['badge:id,code,name,description,rarity,type', 'badge.media'])
            ->orderByDesc('earned_at')
            ->limit($limit)
            ->get()
            ->map(function ($userBadge) {
                return [
                    'id' => $userBadge->badge->id,
                    'code' => $userBadge->badge->code,
                    'name' => $userBadge->badge->name,
                    'description' => $userBadge->badge->description,
                    'rarity' => $userBadge->badge->rarity,
                    'type' => $userBadge->badge->type,
                    'icon_url' => $userBadge->badge->getFirstMediaUrl('icon'),
                    'earned_at' => $userBadge->earned_at,
                ];
            });
    }

    /**
     * Get recommended courses based on enrolled courses
     */
    public function getRecommendedCourses(int $userId, int $limit = 2): Collection
    {
        // Get user's enrolled courses
        $enrolledCourseIds = Enrollment::where('user_id', $userId)
            ->pluck('course_id')
            ->toArray();

        if (empty($enrolledCourseIds)) {
            // If no enrollments, return popular courses
            return $this->getPopularCourses($limit);
        }

        // Get categories and tags from enrolled courses
        $enrolledCourses = Course::whereIn('id', $enrolledCourseIds)
            ->with('tags')
            ->get();

        $categories = $enrolledCourses->pluck('category')->filter()->unique()->toArray();
        $tagIds = $enrolledCourses->flatMap(fn($course) => $course->tags->pluck('id'))->unique()->toArray();

        // Find courses with similar categories or tags
        $query = Course::query()
            ->whereNotIn('id', $enrolledCourseIds)
            ->where('status', 'published')
            ->where('is_active', true);

        if (!empty($categories) || !empty($tagIds)) {
            $query->where(function ($q) use ($categories, $tagIds) {
                if (!empty($categories)) {
                    $q->whereIn('category', $categories);
                }
                if (!empty($tagIds)) {
                    $q->orWhereHas('tags', function ($tagQuery) use ($tagIds) {
                        $tagQuery->whereIn('tags.id', $tagIds);
                    });
                }
            });
        }

        $courses = $query->with(['instructor:id,name', 'media'])
            ->withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->limit($limit)
            ->get();

        // If not enough courses found, fill with popular courses
        if ($courses->count() < $limit) {
            $remaining = $limit - $courses->count();
            $popularCourses = $this->getPopularCourses($remaining, array_merge($enrolledCourseIds, $courses->pluck('id')->toArray()));
            $courses = $courses->merge($popularCourses);
        }

        return $courses->map(function ($course) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'description' => $course->description,
                'category' => $course->category,
                'thumbnail' => $course->getFirstMediaUrl('thumbnail'),
                'instructor' => [
                    'id' => $course->instructor->id ?? null,
                    'name' => $course->instructor->name ?? null,
                ],
                'enrollments_count' => $course->enrollments_count ?? 0,
            ];
        });
    }

    /**
     * Get popular courses
     */
    private function getPopularCourses(int $limit, array $excludeIds = []): Collection
    {
        $query = Course::query()
            ->where('status', 'published')
            ->where('is_active', true);

        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        return $query->with(['instructor:id,name', 'media'])
            ->withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get XP earned this month (from 1st to today)
     */
    private function getXpThisMonth(int $userId): int
    {
        $startOfMonth = now()->startOfMonth();
        $endOfToday = now()->endOfDay();

        return \Modules\Gamification\Models\Point::where('user_id', $userId)
            ->whereBetween('created_at', [$startOfMonth, $endOfToday])
            ->sum('points');
    }

    /**
     * Get next level XP requirement
     */
    private function getNextLevelXp(int $currentLevel): int
    {
        $nextLevel = \Modules\Common\Models\LevelConfig::where('level', $currentLevel + 1)->first();
        return $nextLevel?->xp_required ?? 0;
    }

    /**
     * Calculate level progress percentage
     */
    private function calculateLevelProgress(?UserGamificationStat $stats): float
    {
        if (!$stats) {
            return 0;
        }

        $currentLevelConfig = \Modules\Common\Models\LevelConfig::where('level', $stats->global_level)->first();
        $nextLevelConfig = \Modules\Common\Models\LevelConfig::where('level', $stats->global_level + 1)->first();

        if (!$currentLevelConfig || !$nextLevelConfig) {
            return 0;
        }

        $currentLevelXp = $currentLevelConfig->xp_required;
        $nextLevelXp = $nextLevelConfig->xp_required;
        $userXp = $stats->total_xp;

        $xpInCurrentLevel = $userXp - $currentLevelXp;
        $xpNeededForNextLevel = $nextLevelXp - $currentLevelXp;

        if ($xpNeededForNextLevel <= 0) {
            return 100;
        }

        return round(($xpInCurrentLevel / $xpNeededForNextLevel) * 100, 2);
    }
}
