<?php

namespace Modules\Search\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\Auth\Models\User;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\Unit;
use Modules\Search\Contracts\Repositories\SearchHistoryRepositoryInterface;
use Modules\Search\Contracts\Services\SearchServiceInterface;
use Modules\Search\DTOs\SearchResultDTO;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SearchService implements SearchServiceInterface
{
    public function __construct(
        private readonly SearchHistoryRepositoryInterface $historyRepository,
        private readonly \Modules\Auth\Contracts\Services\UserManagementServiceInterface $userManagementService,
        private readonly \Modules\Schemes\Contracts\Services\CourseServiceInterface $courseService,
        private readonly \Modules\Forums\Contracts\Services\ForumServiceInterface $forumService
    ) {}

    public function search(string $query, array $filters = [], array $sort = [], ?User $user = null, string $type = 'courses'): SearchResultDTO
    {
        $startTime = microtime(true);

        $perPage = $filters['per_page'] ?? 15;
        $perPage = max(1, min($perPage, 100));

        $cleanFilters = collect($filters)->except(['per_page', 'page'])->toArray();

        $request = new Request(['filter' => $cleanFilters]);

        $results = match ($type) {
            'courses' => $this->searchCourses($query, $request, $perPage, $user),
            'units' => $this->searchUnits($query, $request, $perPage, $user),
            'lessons' => $this->searchLessons($query, $request, $perPage, $user),
            'users' => $this->searchUsers($query, $request, $perPage, $user),
            default => $this->searchCourses($query, $request, $perPage, $user),
        };

        $executionTime = microtime(true) - $startTime;

        return new SearchResultDTO(
            items: $results,
            query: $query,
            filters: $filters,
            sort: $sort,
            total: $results->total(),
            executionTime: $executionTime
        );
    }

    protected function searchCourses(string $query, Request $request, int $perPage, ?User $user)
    {
        $builder = QueryBuilder::for(Course::class, $request)
            ->with(['instructor:id,name', 'media'])
            ->withCount('enrollments');

        // Use PgSearchable trait's search method
        if (! empty(trim($query))) {
            $builder->search($query);
        }

        $builder->where('status', 'published');

        return $builder
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('level_tag'),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('instructor_id'),
            ])
            ->allowedSorts(['title', 'created_at', 'updated_at'])
            ->defaultSort('-created_at')
            ->paginate($perPage);
    }

    protected function searchUnits(string $query, Request $request, int $perPage, ?User $user)
    {
        $builder = QueryBuilder::for(Unit::class, $request)
            ->with(['course:id,title,slug']);

        // Use PgSearchable trait's search method
        if (! empty(trim($query))) {
            $builder->search($query);
        }

        return $builder
            ->allowedFilters([
                AllowedFilter::exact('course_id'),
            ])
            ->allowedSorts(['title', 'order', 'created_at'])
            ->defaultSort('order')
            ->paginate($perPage);
    }

    protected function searchLessons(string $query, Request $request, int $perPage, ?User $user)
    {
        if (! $user) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        $builder = QueryBuilder::for(Lesson::class, $request)
            ->with(['unit:id,title,course_id', 'unit.course:id,title']);

        // Use PgSearchable trait's search method
        if (! empty(trim($query))) {
            $builder->search($query);
        }

        if ($user->hasAnyRole(['Admin', 'SuperAdmin'])) {
            // full access
        } elseif ($user->hasRole('Instructor')) {
            $builder->whereHas('unit.course', function (Builder $q) use ($user) {
                $q->where('instructor_id', $user->id);
            });
        } elseif ($user->hasRole('Student')) {
            $builder->whereHas('unit.course.enrollments', function (Builder $q) use ($user) {
                $q->where('user_id', $user->id)
                    ->whereIn('status', ['active', 'completed']);
            });
        } else {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        return $builder
            ->allowedFilters([
                AllowedFilter::exact('unit_id'),
            ])
            ->allowedSorts(['title', 'order', 'created_at'])
            ->defaultSort('order')
            ->paginate($perPage);
    }

    protected function searchUsers(string $query, Request $request, int $perPage, ?User $user)
    {
        if (! $user) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        $builder = QueryBuilder::for(User::class, $request)
            ->with(['roles:id,name,guard_name']);

        // Use PgSearchable trait's search method
        if (! empty(trim($query))) {
            $builder->search($query);
        }

        if ($user->hasAnyRole(['Admin', 'SuperAdmin'])) {
            // full access
        } elseif ($user->hasRole('Instructor') || $user->hasRole('Student')) {
            $builder->whereHas('roles', function (Builder $q) {
                $q->where('name', 'Student');
            });
        } else {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        return $builder
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::callback('role', function (Builder $query, $value) {
                    $query->whereHas('roles', fn ($q) => $q->where('name', $value));
                }),
            ])
            ->allowedSorts(['name', 'email', 'created_at'])
            ->defaultSort('name')
            ->paginate($perPage);
    }

    public function getSuggestions(string $query, int $limit = 10): array
    {
        if (empty(trim($query))) {
            return [];
        }

        // Use PgSearchable trait's search method
        $courses = Course::query()
            ->where('status', 'published')
            ->search($query)
            ->limit($limit)
            ->get();

        return $courses->pluck('title')->unique()->take($limit)->values()->toArray();
    }

    public function saveSearchHistory(User $user, string $query, array $filters = [], int $resultsCount = 0): void
    {
        $query = trim($query);
        if (empty($query)) {
            return;
        }

        $lastSearch = $this->historyRepository->getLastSearchByUser($user->id);

        if ($lastSearch) {
            if ($lastSearch->query === $query) {
                $this->historyRepository->update($lastSearch, [
                    'results_count' => $resultsCount,
                    'created_at' => now(),
                ]);

                return;
            }

            $isTypingForward = str_starts_with(strtolower($query), strtolower($lastSearch->query));
            $isBackspacing = str_starts_with(strtolower($lastSearch->query), strtolower($query));

            if (($isTypingForward || $isBackspacing) && $lastSearch->created_at->diffInSeconds(now()) < 60) {
                $this->historyRepository->update($lastSearch, [
                    'query' => $query,
                    'filters' => $filters,
                    'results_count' => $resultsCount,
                    'created_at' => now(),
                ]);

                return;
            }
        }

        $this->historyRepository->create([
            'user_id' => $user->id,
            'query' => $query,
            'filters' => $filters,
            'results_count' => $resultsCount,
        ]);
    }

    public function globalSearch(string $query, int $limitPerCategory = 5, ?User $user = null): array
    {
        $results = [
            'courses' => collect($this->courseService->searchGlobal($query, $limitPerCategory)),
        ];

        if ($user) {
            $results['users'] = collect($this->userManagementService->searchGlobal($query, $limitPerCategory));
            $results['forums'] = collect($this->forumService->searchGlobal($query, $limitPerCategory));

            if ($user->hasRole('Student') || $user->hasRole('Instructor')) {
                $results['users'] = $results['users']->filter(
                    fn ($searchUser) => $searchUser->hasRole('Student')
                );
            }
        } else {
            $results['users'] = collect([]);
            $results['forums'] = collect([]);
        }

        return $results;
    }
}
