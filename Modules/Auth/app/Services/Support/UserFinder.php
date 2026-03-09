<?php

declare(strict_types=1);

namespace Modules\Auth\Services\Support;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Auth\Contracts\UserAccessPolicyInterface;
use Modules\Auth\Models\User;
use Modules\Auth\Services\UserCacheService;
use Modules\Schemes\Models\CourseAdmin;
use Modules\Schemes\Models\Course;
use Modules\Enrollments\Enums\EnrollmentStatus;
use Modules\Gamification\Models\Leaderboard;
use Modules\Learning\Enums\QuizGradingStatus;
use Modules\Learning\Enums\SubmissionStatus;
use Modules\Learning\Models\QuizSubmission;
use Modules\Learning\Models\Submission;
use Modules\Enrollments\Models\Enrollment;
use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Exceptions\InvalidIncludeQuery;
use Spatie\QueryBuilder\QueryBuilder;

class UserFinder
{
    public function __construct(
        private readonly UserAccessPolicyInterface $userAccessPolicy,
        private readonly UserCacheService $cacheService,
    ) {}

    public function paginate(User $authUser, int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));
        $filters = $search ? ['search' => $search] : [];

        return $this->listUsersForIndex($authUser, $filters, $perPage);
    }

    public function listUsersForIndex(User $authUser, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        if (! $authUser->can('viewAny', User::class)) {
            throw new AuthorizationException(__('messages.unauthorized'));
        }

        $search = data_get($filters, 'search');

        $cleanFilters = Arr::except($filters, ['search']);

        return cache()->tags(['auth', 'users'])->remember(
            "auth:users:index:{$authUser->id}:{$perPage}:".md5(json_encode($filters)),
            300,
            function () use ($authUser, $cleanFilters, $search, $perPage) {
                $requestData = $cleanFilters;
                if (request()->has('include')) {
                    $requestData['include'] = request()->get('include');
                }
                $request = new Request($requestData);

                $query = QueryBuilder::for(User::class, $request)
                    ->select(['id', 'name', 'email', 'username', 'status', 'specialization_id', 'created_at', 'email_verified_at'])
                    ->with(['roles:id,name,guard_name', 'specialization:id,name,value']);

                if ($search && trim((string) $search) !== '') {
                    $query->search($search);
                }

                if ($authUser->hasRole('Admin') && ! $authUser->hasRole('Superadmin')) {
                    // Admin must never receive Superadmin records in index response.
                    $query->whereDoesntHave('roles', function (Builder $roleQuery) {
                        $roleQuery->where('name', 'Superadmin');
                    });

                    $managedCourseIds = CourseAdmin::query()
                        ->where('user_id', $authUser->id)
                        ->pluck('course_id')
                        ->unique();

                    // Get IDs of users (Student/Instructor) created by this admin
                    $createdUserIds = Activity::query()
                        ->where('event', 'created')
                        ->where('subject_type', User::class)
                        ->where('causer_type', User::class)
                        ->where('causer_id', $authUser->id)
                        ->pluck('subject_id')
                        ->unique();

                    // Get instructor IDs teaching in managed courses
                    $instructorIdsInManagedCourses = Course::query()
                        ->whereIn('id', $managedCourseIds)
                        ->whereNotNull('instructor_id')
                        ->pluck('instructor_id')
                        ->unique();

                    $query->where(function (Builder $q) use ($managedCourseIds, $createdUserIds, $instructorIdsInManagedCourses) {
                        // 1. Show all Admins
                        $q->whereHas('roles', function ($roleQuery) {
                            $roleQuery->where('name', 'Admin');
                        })
                            // 2. Show Students with enrollment in managed courses OR created by this admin
                            ->orWhere(function ($studentQuery) use ($managedCourseIds, $createdUserIds) {
                                $studentQuery->whereHas('roles', function ($roleQuery) {
                                    $roleQuery->where('name', 'Student');
                                })->where(function (Builder $studentCondition) use ($managedCourseIds, $createdUserIds) {
                                    // Students with active/completed enrollment
                                    $studentCondition->whereHas('enrollments', function ($enrollmentQuery) use ($managedCourseIds) {
                                        $enrollmentQuery
                                            ->whereIn('course_id', $managedCourseIds)
                                            ->whereIn('status', [
                                                EnrollmentStatus::Active->value,
                                                EnrollmentStatus::Completed->value,
                                            ]);
                                    });

                                    // OR students created by this admin
                                    if ($createdUserIds->isNotEmpty()) {
                                        $studentCondition->orWhereIn('id', $createdUserIds);
                                    }
                                });
                            })
                            // 3. Show Instructors teaching in managed courses OR created by this admin
                            ->orWhere(function ($instructorQuery) use ($instructorIdsInManagedCourses, $createdUserIds) {
                                $instructorQuery->whereHas('roles', function ($roleQuery) {
                                    $roleQuery->where('name', 'Instructor');
                                })->where(function (Builder $instructorCondition) use ($instructorIdsInManagedCourses, $createdUserIds) {
                                    // Instructors teaching in managed courses
                                    if ($instructorIdsInManagedCourses->isNotEmpty()) {
                                        $instructorCondition->whereIn('id', $instructorIdsInManagedCourses);
                                    }

                                    // OR instructors created by this admin
                                    if ($createdUserIds->isNotEmpty()) {
                                        $instructorCondition->orWhereIn('id', $createdUserIds);
                                    }
                                });
                            });
                    });
                } elseif ($authUser->hasRole('Instructor')) {
                    $instructorCourseIds = Course::query()
                        ->where('instructor_id', $authUser->id)
                        ->pluck('id')
                        ->unique();

                    $createdStudentIds = Activity::query()
                        ->where('event', 'created')
                        ->where('subject_type', User::class)
                        ->where('causer_type', User::class)
                        ->where('causer_id', $authUser->id)
                        ->pluck('subject_id')
                        ->unique();

                    $query->whereHas('roles', function ($roleQuery) {
                        $roleQuery->where('name', 'Student');
                    })->where(function (Builder $studentQuery) use ($instructorCourseIds, $createdStudentIds) {
                        $studentQuery->whereHas('enrollments', function ($enrollmentQuery) use ($instructorCourseIds) {
                            $enrollmentQuery
                                ->whereIn('course_id', $instructorCourseIds)
                                ->whereIn('status', [
                                    EnrollmentStatus::Active->value,
                                    EnrollmentStatus::Completed->value,
                                ]);
                        });

                        if ($createdStudentIds->isNotEmpty()) {
                            $studentQuery->orWhereIn('id', $createdStudentIds);
                        }
                    });
                }

                return $query->allowedFilters([
                    AllowedFilter::exact('status'),
                    AllowedFilter::callback('role', function (Builder $query, $value) {
                        $roles = is_array($value)
                          ? $value
                          : Str::of($value)->explode(',')->map(fn ($r) => trim($r))->toArray();
                        $query->whereHas('roles', fn ($q) => $q->whereIn('name', $roles));
                    }),
                    AllowedFilter::callback('search', function (Builder $query, $value) {
                        if (is_string($value) && trim($value) !== '') {
                            $query->search($value);
                        }
                    }),
                ])
                    ->allowedIncludes([
                        // Auth Module
                        'roles',
                        'privacySettings',
                        'enrollments',
                        'managedCourses',
                        // Gamification Module (milestones table has no user_id - global catalog only)
                        'gamificationStats',
                        'badges',
                        'points',
                        'levels',
                        'learningStreaks',
                        // Learning Module
                        'submissions',
                        'assignments',
                        'receivedOverrides',
                        'grantedOverrides',
                        // Forums Module (Post model doesn't exist - only Thread and Reply exist)
                        'threads',
                    ])
                    ->allowedSorts(['name', 'email', 'username', 'status', 'created_at'])
                    ->defaultSort('-created_at')
                    ->paginate($perPage);
            }
        );
    }

    public function showUser(User $authUser, int $userId, ?Request $request = null): User
    {
        $target = $this->cacheService->getUser($userId);

        // Old cached entries may not contain specialization_id (before cache schema update).
        if ($target && ! array_key_exists('specialization_id', $target->getAttributes())) {
            $this->cacheService->invalidateUser($userId);
            $target = null;
        }

        if (! $target) {
            $query = QueryBuilder::for(User::class, $request ?? new Request)
                ->with(['roles:id,name,guard_name', 'specialization:id,name,value'])
                ->allowedIncludes([
                    // Auth Module
                    'roles',
                    'privacySettings',
                    'enrollments',
                    'managedCourses',
                    // Gamification Module (milestones table has no user_id - global catalog only)
                    'gamificationStats',
                    'badges',
                    'points',
                    'levels',
                    'learningStreaks',
                    // Learning Module
                    'submissions',
                    'assignments',
                    'receivedOverrides',
                    'grantedOverrides',
                    // Forums Module (Post model doesn't exist - only Thread and Reply exist)
                    'threads',
                ])
                ->where('id', $userId);

            $target = $query->firstOrFail();

            // Always load course when enrollments is loaded (either via include or already loaded)
            if ($target->relationLoaded('enrollments')) {
                $target->loadMissing('enrollments.course');
            }
        } else {
            // If cached, load roles if not already loaded
            if (! $target->relationLoaded('roles')) {
                $target->load('roles:id,name,guard_name');
            }

            // Load specialization if not already loaded
            if (! $target->relationLoaded('specialization')) {
                $target->load('specialization:id,name,value');
            }

            // Load other requested includes if specified
            if ($request && $request->has('include')) {
                $includes = explode(',', $request->get('include'));
                $includes = array_map('trim', $includes);
                $includes = array_filter($includes); // Remove empty strings

                $allowedIncludes = [
                    'roles', 'privacySettings', 'enrollments', 'managedCourses',
                    'gamificationStats', 'badges',
                    'points', 'levels', 'learningStreaks',
                    'submissions', 'assignments', 'receivedOverrides', 'grantedOverrides',
                    'threads',
                ];

                // Validate includes - throw error if any invalid includes are requested
                $invalidIncludes = array_diff($includes, $allowedIncludes);
                if (! empty($invalidIncludes)) {
                    throw new InvalidIncludeQuery(
                        Collection::make($invalidIncludes),
                        Collection::make($allowedIncludes)
                    );
                }

                // Load valid includes
                if (! empty($includes)) {
                    $target->load($includes);

                    // Always load course when enrollments is loaded
                    if (in_array('enrollments', $includes) && $target->relationLoaded('enrollments')) {
                        $target->loadMissing('enrollments.course');
                    }
                }
            }
        }

        if (! $authUser->can('view', $target)) {
            throw new AuthorizationException(__('messages.auth.no_access_to_user'));
        }

        if ($target->hasRole('Student')) {
            $this->hydrateStudentDetail($target);
        }

        return $target;
    }

    public function listUserEnrolledCourses(User $authUser, int $userId, ?Request $request = null, int $perPage = 15): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));

        $target = User::query()
            ->select(['id'])
            ->with('roles:id,name,guard_name')
            ->findOrFail($userId);

        if (! $authUser->can('view', $target)) {
            throw new AuthorizationException(__('messages.auth.no_access_to_user'));
        }

        return QueryBuilder::for(Enrollment::class, $request ?? new Request)
            ->where('user_id', $userId)
            ->with([
                'course:id,title,slug,code',
                'courseProgress:id,enrollment_id,progress_percent,status',
            ])
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::callback('scheme_name', function (Builder $query, $value) {
                    if (! is_string($value) || trim($value) === '') {
                        return;
                    }

                    $query->whereHas('course', function (Builder $courseQuery) use ($value) {
                        $courseQuery->where('title', 'like', '%'.trim($value).'%');
                    });
                }),
            ])
            ->allowedSorts(['enrolled_at', 'created_at', 'status'])
            ->defaultSort('-enrolled_at')
            ->paginate($perPage);
    }

    private function hydrateStudentDetail(User $target): void
    {
        $target->loadMissing([
            'gamificationStats:user_id,total_xp,global_level',
            'badges' => function ($query) {
                $query->select(['id', 'user_id', 'badge_id', 'earned_at'])
                    ->latest('earned_at')
                    ->limit(3)
                    ->with([
                        'badge:id,code,name,description,type,threshold',
                        'badge.media',
                    ]);
            },
        ]);

        $enrolledCount = $target->enrollments()
            ->whereIn('status', [
                EnrollmentStatus::Pending->value,
                EnrollmentStatus::Active->value,
                EnrollmentStatus::Completed->value,
            ])
            ->count();

        $completedCount = $target->enrollments()
            ->where('status', EnrollmentStatus::Completed->value)
            ->count();

        $gradedAssignmentsCount = Submission::query()
            ->where('user_id', $target->id)
            ->where('status', SubmissionStatus::Graded->value)
            ->count();

        $gradedQuizzesCount = QuizSubmission::query()
            ->where('user_id', $target->id)
            ->where('grading_status', QuizGradingStatus::Graded->value)
            ->count();

        $lastLoginAt = Activity::query()
            ->where('log_name', 'auth')
            ->where('causer_type', User::class)
            ->where('causer_id', $target->id)
            ->latest('created_at')
            ->value('created_at');

        $rank = Leaderboard::query()
            ->whereNull('course_id')
            ->where('user_id', $target->id)
            ->value('rank');

        $target->setAttribute('learning_statistics', [
            'enrolled' => $enrolledCount,
            'completed' => $completedCount,
            'assignments_graded' => $gradedAssignmentsCount,
            'quizzes_graded' => $gradedQuizzesCount,
        ]);
        $target->setAttribute('last_login_at', $lastLoginAt);
        $target->setAttribute('rank', $rank);
        $target->setAttribute('total_xp', $target->gamificationStats?->total_xp ?? 0);
    }
}
