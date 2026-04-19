<?php

declare(strict_types=1);

namespace Modules\Dashboard\Services;

use Illuminate\Support\Collection;
use Modules\Auth\Models\User;
use Modules\Dashboard\Contracts\Repositories\DashboardRepositoryInterface;
use Modules\Dashboard\Contracts\Services\DashboardServiceInterface;
use Modules\Enrollments\Models\Enrollment;
use Modules\Gamification\Models\UserBadge;
use Modules\Gamification\Models\UserGamificationStat;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Lesson;
use Spatie\QueryBuilder\QueryBuilder;

class DashboardService implements DashboardServiceInterface
{
    public function __construct(
        private readonly DashboardRepositoryInterface $dashboardRepository
    ) {
    }

    public function getDashboardData(User $user): array
    {
        if ($user->hasRole('Student')) {
            return [
                'gamification_stats' => $this->dashboardRepository->getStudentGamificationStats($user),
                'latest_learning_activity' => $this->dashboardRepository->getLatestLearningActivity($user),
                'recent_achievements' => $this->dashboardRepository->getRecentAchievements($user),
                'global_top_leaderboard' => $this->dashboardRepository->getTopLeaderboard(),
            ];
        }

        return [
            'pending_enrollment' => $this->dashboardRepository->getPendingEnrollmentCount($user),
            'total_users' => $this->dashboardRepository->getTotalUsersCount($user),
            'total_schemes' => $this->dashboardRepository->getTotalSchemesCount($user),
            'registration_and_class_queue' => $this->dashboardRepository->getRegistrationQueue($user),
            'learning_content_statistic' => $this->dashboardRepository->getContentStatistics($user),
            'global_top_leaderboard' => $this->dashboardRepository->getTopLeaderboard(),
            'latest_posts' => $this->dashboardRepository->getLatestPosts(),
        ];
    }

    
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

        
        $lessonCompletions = \Modules\Enrollments\Models\LessonProgress::query()
            ->join('enrollments', 'lesson_progress.enrollment_id', '=', 'enrollments.id')
            ->where('enrollments.user_id', $userId)
            ->where('lesson_progress.status', 'completed')
            ->select('lesson_progress.*')
            ->get()
            ->groupBy('lesson_id')
            ->map(fn ($group) => $group->first());

        
        $enrollments = $enrollments->sortByDesc(function ($enrollment) use ($lessonCompletions) {
            $courseId = $enrollment->course_id;
            $lessonIds = $enrollment->course->units->flatMap(fn ($unit) => $unit->lessons->pluck('id'));

            
            $mostRecentCompletion = $lessonCompletions
                ->filter(fn ($completion) => $lessonIds->contains($completion->lesson_id))
                ->sortByDesc('updated_at')
                ->first();

            
            return $mostRecentCompletion ? $mostRecentCompletion->updated_at : $enrollment->enrolled_at;
        })->take($limit);

        return $enrollments->map(function ($enrollment) use ($userId) {
            $course = $enrollment->course;

            
            $totalLessons = $course->units->sum(fn ($unit) => $unit->lessons->count());

            
            $completedLessons = \Modules\Enrollments\Models\LessonProgress::query()
                ->join('enrollments', 'lesson_progress.enrollment_id', '=', 'enrollments.id')
                ->where('enrollments.user_id', $userId)
                ->where('lesson_progress.status', 'completed')
                ->whereIn('lesson_progress.lesson_id', $course->units->flatMap(fn ($unit) => $unit->lessons->pluck('id')))
                ->count();

            
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

    
    public function getRecommendedCourses(int $userId, int $limit = 2): Collection
    {
        
        
        $enrolledCourseIds = Enrollment::where('user_id', $userId)
            ->pluck('course_id')
            ->unique()
            ->toArray();

        if (empty($enrolledCourseIds)) {
            
            return $this->getPopularCourses($limit);
        }

        
        $enrolledCourses = Course::whereIn('id', $enrolledCourseIds)->with('category')->get();
        $categoryIds = $enrolledCourses->pluck('category.id')->filter()->unique()->toArray();

        
        $query = Course::query()
            ->whereNotIn('id', $enrolledCourseIds) 
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

    
    private function getXpThisMonth(int $userId): int
    {
        $startOfMonth = now()->startOfMonth();
        $endOfToday = now()->endOfDay();

        return \Modules\Gamification\Models\Point::where('user_id', $userId)
            ->whereBetween('created_at', [$startOfMonth, $endOfToday])
            ->sum('points');
    }

    
    private function getNextLevelXp(int $currentLevel): int
    {
        $nextLevel = \Modules\Common\Models\LevelConfig::where('level', $currentLevel + 1)->first();

        return $nextLevel?->xp_required ?? 0;
    }

    
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

    
    private function getEnrolledCoursesCount(int $userId): int
    {
        return Enrollment::where('user_id', $userId)
            ->where('status', 'active')
            ->count();
    }

    
    private function getLearningHours(int $userId): float
    {
        
        $lessonCount = \Modules\Enrollments\Models\LessonProgress::query()
            ->join('enrollments', 'lesson_progress.enrollment_id', '=', 'enrollments.id')
            ->where('enrollments.user_id', $userId)
            ->where('lesson_progress.status', 'completed')
            ->count();
        $lessonHours = ($lessonCount * 15) / 60;

        
        $quizCount = \Modules\Learning\Models\QuizSubmission::where('user_id', $userId)->count();
        $quizHours = ($quizCount * 20) / 60;

        
        $assignmentCount = \Modules\Learning\Models\Submission::where('user_id', $userId)->count();
        $assignmentHours = ($assignmentCount * 30) / 60;

        $totalHours = $lessonHours + $quizHours + $assignmentHours;

        return round($totalHours, 1);
    }

    
    private function getDaysActive(int $userId): int
    {
        return \Modules\Gamification\Models\Point::where('user_id', $userId)
            ->selectRaw('DATE(created_at) as activity_date')
            ->groupBy('activity_date')
            ->get()
            ->count();
    }

    
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

    
    private function formatActivity(\Modules\Gamification\Models\Point $point): array
    {
        $metadata = is_string($point->metadata) ? json_decode($point->metadata, true) : $point->metadata;

        
        $sourceType = $point->source_type instanceof \BackedEnum
            ? $point->source_type->value
            : $point->source_type;

        $reason = $point->reason instanceof \BackedEnum
            ? $point->reason->value
            : $point->reason;

        
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

    
    private function getLessonTitle(array $metadata): string
    {
        
        if (! empty($metadata['lesson_title'])) {
            return $metadata['lesson_title'];
        }

        if (! empty($metadata['title'])) {
            return $metadata['title'];
        }

        
        if (! empty($metadata['lesson_id'])) {
            $lesson = \Modules\Schemes\Models\Lesson::find($metadata['lesson_id']);
            if ($lesson) {
                return $lesson->title;
            }
        }

        
        if (! empty($metadata['source_name'])) {
            return $metadata['source_name'];
        }

        return 'Lesson';
    }

    
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

        
        if (! empty($metadata['source_name'])) {
            return $metadata['source_name'];
        }

        return 'Unit';
    }

    
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

        
        if (! empty($metadata['submission_id'])) {
            $submission = \Modules\Learning\Models\QuizSubmission::find($metadata['submission_id']);
            if ($submission && $submission->quiz) {
                return $submission->quiz->title;
            }
        }

        
        if (! empty($metadata['source_name'])) {
            return $metadata['source_name'];
        }

        return 'Quiz';
    }

    
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

        
        if (! empty($metadata['submission_id'])) {
            $submission = \Modules\Learning\Models\Submission::find($metadata['submission_id']);
            if ($submission && $submission->assignment) {
                return $submission->assignment->title;
            }
        }

        
        if (! empty($metadata['source_name'])) {
            return $metadata['source_name'];
        }

        return 'Tugas';
    }

    
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
