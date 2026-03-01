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
use Modules\Learning\Models\Question;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\LessonBlock;

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
        } elseif ($user->hasRole('Admin')) {
            $query->whereHas('course', function ($q) use ($user) {
                $q->whereHas('admins', function ($aq) use ($user) {
                    $aq->where('user_id', $user->id);
                });
            });
        }

        return $query->count();
    }

    public function getPendingEnrollmentPercentage(User $user): float
    {
        $totalQuery = Enrollment::query();
        $pendingQuery = Enrollment::where('status', EnrollmentStatus::Pending);

        if ($user->hasRole('Instructor')) {
            $totalQuery->whereHas('course', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            });
            $pendingQuery->whereHas('course', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            });
        } elseif ($user->hasRole('Admin')) {
            $totalQuery->whereHas('course', function ($q) use ($user) {
                $q->whereHas('admins', function ($aq) use ($user) {
                    $aq->where('user_id', $user->id);
                });
            });
            $pendingQuery->whereHas('course', function ($q) use ($user) {
                $q->whereHas('admins', function ($aq) use ($user) {
                    $aq->where('user_id', $user->id);
                });
            });
        }

        $total = $totalQuery->count();
        $pending = $pendingQuery->count();

        return $total > 0 ? round(($pending / $total) * 100, 2) : 0;
    }

    public function getTotalUsersCount(User $user): int
    {
        $query = User::role('Student')->whereHas('enrollments', function ($eq) use ($user) {
            $eq->where('status', EnrollmentStatus::Active);

            if ($user->hasRole('Instructor')) {
                $eq->whereHas('course', function ($cq) use ($user) {
                    $cq->where('instructor_id', $user->id);
                });
            } elseif ($user->hasRole('Admin')) {
                $eq->whereHas('course', function ($cq) use ($user) {
                    $cq->whereHas('admins', function ($aq) use ($user) {
                        $aq->where('user_id', $user->id);
                    });
                });
            }
        });

        return $query->count();
    }

    public function getTotalUsersPercentage(User $user): float
    {
        $totalStudents = User::role('Student')->count();
        $activeUsers = $this->getTotalUsersCount($user);

        return $totalStudents > 0 ? round(($activeUsers / $totalStudents) * 100, 2) : 0;
    }

    public function getTotalSchemesCount(User $user): int
    {
        $query = Course::where('status', \Modules\Schemes\Enums\CourseStatus::Published);

        if ($user->hasRole('Instructor')) {
            $query->where('instructor_id', $user->id);
        } elseif ($user->hasRole('Admin')) {
            $query->whereHas('admins', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        return $query->count();
    }

    public function getTotalSchemesPercentage(User $user): float
    {
        $totalCourses = Course::count();
        $publishedSchemes = $this->getTotalSchemesCount($user);

        return $totalCourses > 0 ? round(($publishedSchemes / $totalCourses) * 100, 2) : 0;
    }

    public function getRegistrationQueue(User $user, int $limit = 5): array
    {
        $query = Enrollment::with(['user', 'course'])
            ->latest();

        if ($user->hasRole('Instructor')) {
            $query->whereHas('course', function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            });
        } elseif ($user->hasRole('Admin')) {
            $query->whereHas('course', function ($q) use ($user) {
                $q->whereHas('admins', function ($aq) use ($user) {
                    $aq->where('user_id', $user->id);
                });
            });
        }

        return $query->limit($limit)->get()->map(fn ($enrollment) => [
            'status' => $enrollment->status->value,
            'user_name' => $enrollment->user?->name,
            'course_name' => $enrollment->course?->title,
            'date' => $enrollment->created_at,
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
        } elseif ($user->hasRole('Admin')) {
            $lessonBlockQuery->whereHas('lesson.unit.course', function ($q) use ($user) {
                $q->whereHas('admins', function ($aq) use ($user) {
                    $aq->where('user_id', $user->id);
                });
            });

            $questionQuery->whereHas('assignment.lesson.unit.course.admins', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $blockStats = $lessonBlockQuery->select('block_type', DB::raw('count(*) as total'))
            ->groupBy('block_type')
            ->pluck('total', 'block_type')
            ->toArray();

        // Calculate total blocks and percentages
        $totalBlocks = array_sum($blockStats);
        $blockPercentages = [];
        foreach ($blockStats as $type => $count) {
            $blockPercentages[$type] = $totalBlocks > 0 ? round(($count / $totalBlocks) * 100, 2) : 0;
        }

        // Get question types breakdown
        $questionTypes = $questionQuery->select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        $totalQuestions = $questionQuery->count();

        // Calculate question type counts and percentages
        $questionByType = [
            'multiple_choice' => $questionTypes['multiple_choice'] ?? 0,
            'checkbox' => $questionTypes['checkbox'] ?? 0,
            'essay' => $questionTypes['essay'] ?? 0,
            'file_upload' => $questionTypes['file_upload'] ?? 0,
        ];

        $questionPercentages = [];
        foreach ($questionByType as $type => $count) {
            $questionPercentages[$type] = $totalQuestions > 0 ? round(($count / $totalQuestions) * 100, 2) : 0;
        }

        return [
            'blocks' => [
                'counts' => $blockStats,
                'percentages' => $blockPercentages,
                'total' => $totalBlocks,
            ],
            'question_bank' => [
                'total' => $totalQuestions,
                'by_type' => [
                    'counts' => $questionByType,
                    'percentages' => $questionPercentages,
                ],
            ],
        ];
    }

    public function getTopLeaderboard(int $limit = 6): array
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

    public function getRecommendedCourses(User $user): array
    {
        $latestEnrollment = Enrollment::where('user_id', $user->id)
            ->where('status', EnrollmentStatus::Active)
            ->with('course')
            ->orderByDesc('updated_at')
            ->first();

        if (! $latestEnrollment || ! $latestEnrollment->course) {
            return Course::where('status', \Modules\Schemes\Enums\CourseStatus::Published)
                ->with(['instructor', 'media'])
                ->withCount('enrollments')
                ->inRandomOrder()
                ->limit(2)
                ->get()
                ->map(fn ($course) => [
                    'id' => $course->id,
                    'slug' => $course->slug,
                    'title' => $course->title,
                    'short_desc' => $course->short_desc,
                    'thumbnail' => $course->getFirstMediaUrl('thumbnail'),
                    'instructor' => [
                        'id' => $course->instructor?->id,
                        'name' => $course->instructor?->name,
                    ],
                    'enrollments_count' => $course->enrollments_count,
                ])
                ->toArray();
        }

        $lastCourse = $latestEnrollment->course;
        $categoryId = $lastCourse->category_id;
        $enrolledCourseIds = Enrollment::where('user_id', $user->id)->pluck('course_id')->toArray();

        $tagIds = \DB::table('course_tag')
            ->where('course_id', $lastCourse->id)
            ->pluck('tag_id')
            ->toArray();

        $recommended = Course::where('status', \Modules\Schemes\Enums\CourseStatus::Published)
            ->where('id', '!=', $lastCourse->id)
            ->whereNotIn('id', $enrolledCourseIds)
            ->where(function ($query) use ($categoryId, $tagIds) {
                if ($categoryId) {
                    $query->where('category_id', $categoryId);
                }

                if (! empty($tagIds)) {
                    $query->orWhereExists(function ($subQuery) use ($tagIds) {
                        $subQuery->select(\DB::raw(1))
                            ->from('course_tag')
                            ->whereColumn('course_tag.course_id', 'courses.id')
                            ->whereIn('course_tag.tag_id', $tagIds);
                    });
                }
            })
            ->with(['instructor', 'media'])
            ->withCount('enrollments')
            ->inRandomOrder()
            ->limit(2)
            ->get();

        if ($recommended->count() < 2) {
            $additionalCount = 2 - $recommended->count();
            $excludeIds = array_merge($enrolledCourseIds, [$lastCourse->id], $recommended->pluck('id')->toArray());

            $additional = Course::where('status', \Modules\Schemes\Enums\CourseStatus::Published)
                ->whereNotIn('id', $excludeIds)
                ->with(['instructor', 'media'])
                ->withCount('enrollments')
                ->inRandomOrder()
                ->limit($additionalCount)
                ->get();

            $recommended = $recommended->merge($additional);
        }

        return $recommended->map(fn ($course) => [
            'id' => $course->id,
            'slug' => $course->slug,
            'title' => $course->title,
            'short_desc' => $course->short_desc,
            'thumbnail' => $course->getFirstMediaUrl('thumbnail'),
            'instructor' => [
                'id' => $course->instructor?->id,
                'name' => $course->instructor?->name,
            ],
            'enrollments_count' => $course->enrollments_count,
        ])->toArray();
    }
}
