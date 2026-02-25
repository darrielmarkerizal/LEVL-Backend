<?php

declare(strict_types=1);

namespace Modules\Dashboard\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Dashboard\Contracts\Repositories\DashboardRepositoryInterface;
use Modules\Enrollments\Enums\EnrollmentStatus;
use Modules\Enrollments\Models\Enrollment;
use Modules\Enrollments\Models\LessonProgress;
use Modules\Gamification\Models\Point;
use Modules\Gamification\Models\UserBadge;
use Modules\Gamification\Models\UserGamificationStat;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\LessonBlock;
use Modules\Learning\Models\Question;

class DashboardRepository extends BaseRepository implements DashboardRepositoryInterface
{
    protected function model(): string
    {
        return Enrollment::class;
    }

    public function getPendingEnrollmentCount(User $user): int
    {
        $query = Enrollment::where('status', EnrollmentStatus::Pending);

        if ($user->hasRole('Instructor')) {
            $query->whereHas('course', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            });
        }

        return $query->count();
    }

    public function getTotalUsersCount(User $user): int
    {
        if ($user->hasRole(['Superadmin', 'Admin'])) {
            return User::count();
        }

        return Enrollment::whereHas('course', function ($q) use ($user) {
            $q->where('instructor_id', $user->id);
        })->distinct('user_id')->count('user_id');
    }

    public function getTotalSchemesCount(User $user): int
    {
        $query = Course::query();

        if ($user->hasRole('Instructor')) {
            $query->where('instructor_id', $user->id);
        }

        return $query->count();
    }

    public function getRegistrationQueue(User $user, int $limit = 10): array
    {
        $query = Enrollment::with(['user', 'course'])
            ->latest();

        if ($user->hasRole('Instructor')) {
            $query->whereHas('course', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            });
        }

        return $query->limit($limit)->get()->map(fn ($enrollment) => [
            'status' => $enrollment->status->value,
            'user_name' => $enrollment->user?->name,
            'scheme' => $enrollment->course?->title,
        ])->toArray();
    }

    public function getContentStatistics(User $user): array
    {
        $lessonBlockQuery = LessonBlock::query();
        $questionQuery = Question::query();

        if ($user->hasRole('Instructor')) {
            $lessonBlockQuery->whereHas('lesson.unit.course', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            });
            
            $questionQuery->whereHas('assignment', function ($q) use ($user) {
                $q->where('created_by', $user->id);
            });
        }

        $stats = $lessonBlockQuery->select('block_type', DB::raw('count(*) as total'))
            ->groupBy('block_type')
            ->pluck('total', 'block_type')
            ->toArray();

        return [
            'blocks' => $stats,
            'question_bank_count' => $questionQuery->count(),
        ];
    }

    public function getTopLeaderboard(int $limit = 3): array
    {
        return Point::with('user')
            ->selectRaw('user_id, SUM(points) as total_points')
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->limit($limit)
            ->get()
            ->map(fn ($p) => [
                'user' => [
                    'id' => $p->user?->id,
                    'name' => $p->user?->name,
                    'avatar' => $p->user?->avatar_url,
                ],
                'total_points' => (int) $p->total_points,
            ])
            ->toArray();
    }

    public function getStudentGamificationStats(User $user): array
    {
        $stats = UserGamificationStat::where('user_id', $user->id)->first();

        return [
            'day_streak' => $stats?->current_streak ?? 0,
            'xp' => $stats?->total_xp ?? 0,
            'level' => $stats?->global_level ?? 1,
            'current_level_xp' => $stats?->current_level_xp ?? 0,
            'xp_to_next_level' => $stats?->xp_to_next_level ?? 100,
            'progress_percent' => $stats?->progress_to_next_level ?? 0,
        ];
    }

    public function getLatestLearningActivity(User $user): ?array
    {
        $latest = LessonProgress::whereHas('enrollment', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->with(['lesson.unit.course'])
            ->orderByDesc('updated_at')
            ->first();

        if (! $latest) {
            return null;
        }

        $lesson = $latest->lesson;
        $unit = $lesson->unit;
        $course = $unit->course;

        $lessonIndex = $unit->lessons()->where('order', '<=', $lesson->order)->count();
        $totalLessons = $unit->lessons()->count();

        return [
            'course' => $course->title,
            'unit' => $unit->title,
            'lesson_index' => $lessonIndex,
            'total_lessons' => $totalLessons,
            'updated_at' => $latest->updated_at,
        ];
    }

    public function getRecentAchievements(User $user): array
    {
        return UserBadge::where('user_id', $user->id)
            ->with('badge')
            ->orderByDesc('earned_at')
            ->limit(4)
            ->get()
            ->map(fn ($ub) => [
                'name' => $ub->badge->name,
                'image' => $ub->badge->icon_url,
                'earned_at' => $ub->earned_at,
            ])
            ->toArray();
    }
}
