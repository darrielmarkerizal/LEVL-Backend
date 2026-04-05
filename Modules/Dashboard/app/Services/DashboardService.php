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
            'courses' => [
                'enrolled' => $this->getEnrolledCoursesCount($userId),
            ],
            'learning_hours' => $this->getLearningHours($userId),
            'days_active' => $this->getDaysActive($userId),
            'recent_activity' => $this->getRecentActivity($userId, 3),
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
        $lessonCompletions = \Modules\Enrollments\Models\LessonProgress::query()
            ->join('enrollments', 'lesson_progress.enrollment_id', '=', 'enrollments.id')
            ->where('enrollments.user_id', $userId)
            ->where('lesson_progress.status', 'completed')
            ->select('lesson_progress.*')
            ->get()
            ->groupBy('lesson_id')
            ->map(fn ($group) => $group->first());

        // Sort enrollments by most recent lesson completion or enrolled_at
        $enrollments = $enrollments->sortByDesc(function ($enrollment) use ($lessonCompletions) {
            $courseId = $enrollment->course_id;
            $lessonIds = $enrollment->course->units->flatMap(fn ($unit) => $unit->lessons->pluck('id'));

            // Find most recent lesson completion for this course
            $mostRecentCompletion = $lessonCompletions
                ->filter(fn ($completion) => $lessonIds->contains($completion->lesson_id))
                ->sortByDesc('updated_at')
                ->first();

            // Return most recent completion time or enrolled_at
            return $mostRecentCompletion ? $mostRecentCompletion->updated_at : $enrollment->enrolled_at;
        })->take($limit);

        return $enrollments->map(function ($enrollment) use ($userId) {
            $course = $enrollment->course;

            // Get total lessons
            $totalLessons = $course->units->sum(fn ($unit) => $unit->lessons->count());

            // Get completed lessons
            $completedLessons = \Modules\Enrollments\Models\LessonProgress::query()
                ->join('enrollments', 'lesson_progress.enrollment_id', '=', 'enrollments.id')
                ->where('enrollments.user_id', $userId)
                ->where('lesson_progress.status', 'completed')
                ->whereIn('lesson_progress.lesson_id', $course->units->flatMap(fn ($unit) => $unit->lessons->pluck('id')))
                ->count();

            // Get last accessed lesson
            $lastLesson = \Modules\Enrollments\Models\LessonProgress::query()
                ->join('enrollments', 'lesson_progress.enrollment_id', '=', 'enrollments.id')
                ->where('enrollments.user_id', $userId)
                ->where('lesson_progress.status', 'completed')
                ->whereIn('lesson_progress.lesson_id', $course->units->flatMap(fn ($unit) => $unit->lessons->pluck('id')))
                ->select('lesson_progress.*')
                ->latest('lesson_progress.updated_at')
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
     * Only returns courses that user has NOT enrolled in
     */
    public function getRecommendedCourses(int $userId, int $limit = 2): Collection
    {
        // Get user's enrolled courses (all statuses: pending, active, completed, rejected)
        // We exclude ALL enrolled courses, not just active ones
        $enrolledCourseIds = Enrollment::where('user_id', $userId)
            ->pluck('course_id')
            ->unique()
            ->toArray();

        if (empty($enrolledCourseIds)) {
            // If no enrollments, return popular courses
            return $this->getPopularCourses($limit);
        }

        // Get category IDs from enrolled courses
        $enrolledCourses = Course::whereIn('id', $enrolledCourseIds)->with('category')->get();
        $categoryIds = $enrolledCourses->pluck('category.id')->filter()->unique()->toArray();

        // Find courses with similar categories, excluding ALL enrolled courses
        $query = Course::query()
            ->whereNotIn('id', $enrolledCourseIds) // Exclude all enrolled courses
            ->where('status', 'published');

        if (! empty($categoryIds)) {
            $query->whereHas('category', function ($q) use ($categoryIds) {
                $q->whereIn('id', $categoryIds);
            });
        }

        $courses = $query->with(['instructor:id,name', 'media', 'category'])
            ->withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->limit($limit)
            ->get();

        // If not enough courses found, fill with popular courses (also excluding enrolled)
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
                'description' => $course->short_desc,
                'category' => $course->category?->name,
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
            ->where('status', 'published');

        if (! empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        return $query->with(['instructor:id,name', 'media', 'category'])
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
        if (! $stats) {
            return 0;
        }

        $currentLevelConfig = \Modules\Common\Models\LevelConfig::where('level', $stats->global_level)->first();
        $nextLevelConfig = \Modules\Common\Models\LevelConfig::where('level', $stats->global_level + 1)->first();

        if (! $currentLevelConfig || ! $nextLevelConfig) {
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

    /**
     * Get enrolled courses count
     */
    private function getEnrolledCoursesCount(int $userId): int
    {
        return Enrollment::where('user_id', $userId)
            ->where('status', 'active')
            ->count();
    }

    /**
     * Get total learning hours
     * Calculated from lesson completions and assignment/quiz submissions
     */
    private function getLearningHours(int $userId): float
    {
        // Count lesson completions (assume 15 minutes per lesson)
        $lessonCount = \Modules\Enrollments\Models\LessonProgress::query()
            ->join('enrollments', 'lesson_progress.enrollment_id', '=', 'enrollments.id')
            ->where('enrollments.user_id', $userId)
            ->where('lesson_progress.status', 'completed')
            ->count();
        $lessonHours = ($lessonCount * 15) / 60;

        // Count quiz submissions (assume 20 minutes per quiz)
        $quizCount = \Modules\Learning\Models\QuizSubmission::where('user_id', $userId)->count();
        $quizHours = ($quizCount * 20) / 60;

        // Count assignment submissions (assume 30 minutes per assignment)
        $assignmentCount = \Modules\Learning\Models\Submission::where('user_id', $userId)->count();
        $assignmentHours = ($assignmentCount * 30) / 60;

        $totalHours = $lessonHours + $quizHours + $assignmentHours;

        return round($totalHours, 1);
    }

    /**
     * Get days active (days with any XP earning activity)
     */
    private function getDaysActive(int $userId): int
    {
        return \Modules\Gamification\Models\Point::where('user_id', $userId)
            ->selectRaw('DATE(created_at) as activity_date')
            ->groupBy('activity_date')
            ->get()
            ->count();
    }

    /**
     * Get recent activity (XP earning activities)
     */
    private function getRecentActivity(int $userId, int $limit = 3): array
    {
        $points = \Modules\Gamification\Models\Point::where('user_id', $userId)
            ->where('points', '>', 0)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return $points->map(function ($point) {
            $activity = $this->formatActivity($point);

            return [
                'type' => $activity['type'],
                'description' => $activity['description'],
                'xp_earned' => $point->points,
                'timestamp' => $point->created_at->toISOString(),
            ];
        })->toArray();
    }

    /**
     * Format activity based on source type and metadata
     */
    private function formatActivity(\Modules\Gamification\Models\Point $point): array
    {
        $metadata = is_string($point->metadata) ? json_decode($point->metadata, true) : $point->metadata;

        // Convert enum to string if needed
        $sourceType = $point->source_type instanceof \BackedEnum
            ? $point->source_type->value
            : $point->source_type;

        $reason = $point->reason instanceof \BackedEnum
            ? $point->reason->value
            : $point->reason;

        // Determine activity type and description based on source_type and reason
        $type = 'activity';
        $description = __('messages.activity.default');

        switch ($sourceType) {
            case 'lesson':
                $type = 'lesson_completion';
                $lessonTitle = $this->getLessonTitle($metadata);
                $description = __('messages.activity.lesson_completion', ['title' => $lessonTitle]);
                break;

            case 'unit':
                $type = 'unit_completion';
                $unitTitle = $this->getUnitTitle($metadata);
                $description = __('messages.activity.unit_completion', ['title' => $unitTitle]);
                break;

            case 'quiz':
            case 'quiz_submission':
                $type = 'quiz_completion';
                $quizTitle = $this->getQuizTitle($metadata);

                if (isset($metadata['score']) && $metadata['score'] == 100) {
                    $description = __('messages.activity.quiz_perfect_score', ['title' => $quizTitle]);
                } else {
                    $description = __('messages.activity.quiz_completion', ['title' => $quizTitle]);
                }
                break;

            case 'assignment':
            case 'assignment_submission':
                $type = 'assignment_submission';
                $assignmentTitle = $this->getAssignmentTitle($metadata);
                $description = __('messages.activity.assignment_submission', ['title' => $assignmentTitle]);
                break;

            case 'badge':
                $type = 'badge_earned';
                $badgeName = $this->getBadgeName($metadata);
                $description = __('messages.activity.badge_earned', ['name' => $badgeName]);
                break;

            case 'forum':
                if ($reason === 'thread_created') {
                    $type = 'forum_thread';
                    $description = __('messages.activity.forum_thread');
                } elseif ($reason === 'reply_created') {
                    $type = 'forum_reply';
                    $description = __('messages.activity.forum_reply');
                } elseif ($reason === 'reaction_received') {
                    $type = 'forum_reaction';
                    $description = __('messages.activity.forum_reaction');
                }
                break;

            case 'streak':
                $type = 'streak_bonus';
                $days = $metadata['days'] ?? $metadata['streak_days'] ?? 0;
                $description = __('messages.activity.streak_bonus', ['days' => $days]);
                break;

            default:
                // Use reason as fallback
                if ($reason) {
                    $description = ucfirst(str_replace('_', ' ', $reason));
                }
                break;
        }

        return [
            'type' => $type,
            'description' => $description,
        ];
    }

    /**
     * Get lesson title from metadata or database
     */
    private function getLessonTitle(array $metadata): string
    {
        // Try to get from metadata first
        if (! empty($metadata['lesson_title'])) {
            return $metadata['lesson_title'];
        }

        if (! empty($metadata['title'])) {
            return $metadata['title'];
        }

        // Try to get from database using lesson_id
        if (! empty($metadata['lesson_id'])) {
            $lesson = \Modules\Schemes\Models\Lesson::find($metadata['lesson_id']);
            if ($lesson) {
                return $lesson->title;
            }
        }

        // Use source_name from seeded data as fallback
        if (! empty($metadata['source_name'])) {
            return $metadata['source_name'];
        }

        return 'Lesson';
    }

    /**
     * Get unit title from metadata or database
     */
    private function getUnitTitle(array $metadata): string
    {
        if (! empty($metadata['unit_title'])) {
            return $metadata['unit_title'];
        }

        if (! empty($metadata['title'])) {
            return $metadata['title'];
        }

        if (! empty($metadata['unit_id'])) {
            $unit = \Modules\Schemes\Models\Unit::find($metadata['unit_id']);
            if ($unit) {
                return $unit->title;
            }
        }

        // Use source_name from seeded data as fallback
        if (! empty($metadata['source_name'])) {
            return $metadata['source_name'];
        }

        return 'Unit';
    }

    /**
     * Get quiz title from metadata or database
     */
    private function getQuizTitle(array $metadata): string
    {
        if (! empty($metadata['quiz_title'])) {
            return $metadata['quiz_title'];
        }

        if (! empty($metadata['title'])) {
            return $metadata['title'];
        }

        if (! empty($metadata['quiz_id'])) {
            $quiz = \Modules\Learning\Models\Quiz::find($metadata['quiz_id']);
            if ($quiz) {
                return $quiz->title;
            }
        }

        // Try to get from sourceable (for real data, not seeded)
        if (! empty($metadata['submission_id'])) {
            $submission = \Modules\Learning\Models\QuizSubmission::find($metadata['submission_id']);
            if ($submission && $submission->quiz) {
                return $submission->quiz->title;
            }
        }

        // Use source_name from seeded data as fallback
        if (! empty($metadata['source_name'])) {
            return $metadata['source_name'];
        }

        return 'Quiz';
    }

    /**
     * Get assignment title from metadata or database
     */
    private function getAssignmentTitle(array $metadata): string
    {
        if (! empty($metadata['assignment_title'])) {
            return $metadata['assignment_title'];
        }

        if (! empty($metadata['title'])) {
            return $metadata['title'];
        }

        if (! empty($metadata['assignment_id'])) {
            $assignment = \Modules\Learning\Models\Assignment::find($metadata['assignment_id']);
            if ($assignment) {
                return $assignment->title;
            }
        }

        // Try to get from sourceable (for real data, not seeded)
        if (! empty($metadata['submission_id'])) {
            $submission = \Modules\Learning\Models\Submission::find($metadata['submission_id']);
            if ($submission && $submission->assignment) {
                return $submission->assignment->title;
            }
        }

        // Use source_name from seeded data as fallback
        if (! empty($metadata['source_name'])) {
            return $metadata['source_name'];
        }

        return 'Tugas';
    }

    /**
     * Get badge name from metadata or database
     */
    private function getBadgeName(array $metadata): string
    {
        if (! empty($metadata['badge_name'])) {
            return $metadata['badge_name'];
        }

        if (! empty($metadata['name'])) {
            return $metadata['name'];
        }

        if (! empty($metadata['badge_id'])) {
            $badge = \Modules\Gamification\Models\Badge::find($metadata['badge_id']);
            if ($badge) {
                return $badge->name;
            }
        }

        return 'Badge';
    }
}
