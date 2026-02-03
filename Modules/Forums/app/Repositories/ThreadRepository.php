<?php

declare(strict_types=1);

namespace Modules\Forums\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Modules\Forums\Models\Thread;

class ThreadRepository extends BaseRepository
{
    protected function model(): string
    {
        return Thread::class;
    }

    protected array $allowedFilters = ['author_id', 'pinned', 'resolved', 'closed'];

    protected array $allowedSorts = ['id', 'created_at', 'last_activity_at', 'is_pinned', 'views_count', 'replies_count'];

    protected string $defaultSort = '-last_activity_at';

    protected array $with = ['author'];

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query();

        $query = $this->applyFilters($query, $filters);
        $query = $this->applySorting($query, $filters);

        $perPage = $filters['per_page'] ?? $perPage;

        return $query->paginate($perPage);
    }

    public function find(int $id): ?Thread
    {
        return Thread::find($id);
    }

    public function findWithReplies(int $id): ?Thread
    {
        return $this->findWithRelations($id);
    }

    public function create(array $data): Thread
    {
        $thread = Thread::create($data);
        $thread->updateLastActivity();

        return $thread;
    }

    public function pin(Thread $thread): Thread
    {
        $thread->is_pinned = true;
        $thread->save();

        return $thread;
    }

    public function unpin(Thread $thread): Thread
    {
        $thread->is_pinned = false;
        $thread->save();

        return $thread;
    }

    public function lock(Thread $thread): Thread
    {
        $thread->is_closed = true;
        $thread->save();

        return $thread;
    }

    public function unlock(Thread $thread): Thread
    {
        $thread->is_closed = false;
        $thread->save();

        return $thread;
    }

    public function getThreadsByForumable(string $forumableType, int $forumableId, array $filters = []): LengthAwarePaginator
    {
        $query = Thread::query()
            ->where('forumable_type', $forumableType)
            ->where('forumable_id', $forumableId)
            ->with(['author', 'replies'])
            ->withCount('replies');

        return $this->filteredPaginate(
            $query,
            $filters,
            [
                AllowedFilter::exact('author_id'),
                AllowedFilter::scope('pinned'),
                AllowedFilter::scope('resolved'),
                AllowedFilter::scope('closed'),
            ],
            ['last_activity_at', 'created_at', 'replies_count', 'views_count'],
            '-last_activity_at',
            $filters['per_page'] ?? 20
        );
    }

    public function getThreadsForScheme(int $schemeId, array $filters = []): LengthAwarePaginator
    {
        return $this->getThreadsByForumable(\Modules\Schemes\Models\Course::class, $schemeId, $filters);
    }

    public function searchThreadsByForumable(string $searchQuery, string $forumableType, int $forumableId, array $filters = []): LengthAwarePaginator
    {
        $ids = Thread::search($searchQuery)
            ->where('forumable_type', $forumableType)
            ->where('forumable_id', $forumableId)
            ->keys()
            ->toArray();

        $query = Thread::query()
            ->whereIn('id', $ids)
            ->with(['author', 'replies'])
            ->withCount('replies');

        return $this->filteredPaginate(
            $query,
            $filters,
            [
                AllowedFilter::exact('author_id'),
                AllowedFilter::scope('pinned'),
                AllowedFilter::scope('resolved'),
                AllowedFilter::scope('closed'),
            ],
            ['last_activity_at', 'created_at', 'replies_count', 'views_count'],
            '-last_activity_at',
            $filters['per_page'] ?? 20
        );
    }

    public function searchThreads(string $searchQuery, int $schemeId, array $filters = []): LengthAwarePaginator
    {
        return $this->searchThreadsByForumable($searchQuery, \Modules\Schemes\Models\Course::class, $schemeId, $filters);
    }

    public function findWithRelations(int $threadId): ?Thread
    {
        return Thread::with(['author', 'forumable', 'replies.author', 'replies.children'])
            ->withCount('replies')
            ->find($threadId);
    }

    public function getPinnedThreads(string $forumableType, int $forumableId): Collection
    {
        return Thread::query()
            ->where('forumable_type', $forumableType)
            ->where('forumable_id', $forumableId)
            ->pinned()
            ->with(['author'])
            ->withCount('replies')
            ->orderBy('last_activity_at', 'desc')
            ->get();
    }

    public function getAllThreads(array $filters = [], ?string $search = null): LengthAwarePaginator
    {
        $query = Thread::query()
            ->with(['author', 'forumable'])
            ->withCount('replies');

        if ($search) {
            $ids = Thread::search($search)->keys()->toArray();
            $query->whereIn('id', $ids);
        }

        return $this->filteredPaginate(
            $query,
            $filters,
            [
                AllowedFilter::exact('author_id'),
                AllowedFilter::scope('pinned'),
                AllowedFilter::scope('resolved'),
                AllowedFilter::scope('closed'),
            ],
            ['last_activity_at', 'created_at', 'replies_count', 'views_count'],
            '-last_activity_at',
            $filters['per_page'] ?? 20
        );
    }

    public function getInstructorThreads(int $instructorId, array $filters = [], ?string $search = null): LengthAwarePaginator
    {
        $courseIds = \Modules\Schemes\Models\Course::where('instructor_id', $instructorId)
            ->pluck('id')
            ->toArray();

        $query = Thread::query()
            ->where('forumable_type', \Modules\Schemes\Models\Course::class)
            ->whereIn('forumable_id', $courseIds)
            ->with(['author', 'forumable'])
            ->withCount('replies');

        if ($search) {
            $ids = Thread::search($search)
                ->where('forumable_type', \Modules\Schemes\Models\Course::class)
                ->keys()
                ->toArray();
            $query->whereIn('id', $ids);
        }

        return $this->filteredPaginate(
            $query,
            $filters,
            [
                AllowedFilter::exact('author_id'),
                AllowedFilter::scope('pinned'),
                AllowedFilter::scope('resolved'),
                AllowedFilter::scope('closed'),
            ],
            ['last_activity_at', 'created_at', 'replies_count', 'views_count'],
            '-last_activity_at',
            $filters['per_page'] ?? 20
        );
    }

    public function getUserThreads(int $userId, array $filters = [], ?string $search = null): LengthAwarePaginator
    {
        $query = Thread::query()
            ->where('author_id', $userId)
            ->with(['author', 'forumable'])
            ->withCount('replies');

        if ($search) {
            $ids = Thread::search($search)
                ->where('author_id', $userId)
                ->keys()
                ->toArray();
            $query->whereIn('id', $ids);
        }

        return $this->filteredPaginate(
            $query,
            $filters,
            [
                AllowedFilter::scope('pinned'),
                AllowedFilter::scope('resolved'),
                AllowedFilter::scope('closed'),
            ],
            ['last_activity_at', 'created_at', 'replies_count', 'views_count'],
            '-last_activity_at',
            $filters['per_page'] ?? 20
        );
    }

    public function getRecentThreads(int $limit = 10): Collection
    {
        return Thread::query()
            ->with(['author', 'forumable'])
            ->withCount('replies')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getInstructorRecentThreads(int $instructorId, int $limit = 10): Collection
    {
        $courseIds = \Modules\Schemes\Models\Course::where('instructor_id', $instructorId)
            ->pluck('id')
            ->toArray();

        return Thread::query()
            ->where('forumable_type', \Modules\Schemes\Models\Course::class)
            ->whereIn('forumable_id', $courseIds)
            ->with(['author', 'forumable'])
            ->withCount('replies')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getTrendingThreads(int $limit = 10, string $period = '7days'): Collection
    {
        $startDate = match($period) {
            '24hours' => now()->subDay(),
            '7days' => now()->subDays(7),
            '30days' => now()->subDays(30),
            '90days' => now()->subDays(90),
            default => now()->subDays(7),
        };

        return Thread::query()
            ->where('created_at', '>=', $startDate)
            ->with(['author', 'forumable'])
            ->withCount('replies')
            ->orderBy('replies_count', 'desc')
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getInstructorTrendingThreads(int $instructorId, int $limit = 10, string $period = '7days'): Collection
    {
        $courseIds = \Modules\Schemes\Models\Course::where('instructor_id', $instructorId)
            ->pluck('id')
            ->toArray();

        $startDate = match($period) {
            '24hours' => now()->subDay(),
            '7days' => now()->subDays(7),
            '30days' => now()->subDays(30),
            '90days' => now()->subDays(90),
            default => now()->subDays(7),
        };

        return Thread::query()
            ->where('forumable_type', \Modules\Schemes\Models\Course::class)
            ->whereIn('forumable_id', $courseIds)
            ->where('created_at', '>=', $startDate)
            ->with(['author', 'forumable'])
            ->withCount('replies')
            ->orderBy('replies_count', 'desc')
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();
    }

