<?php

declare(strict_types=1);

namespace Modules\Forums\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Auth\Models\User;
use Modules\Forums\Contracts\Repositories\ThreadRepositoryInterface;
use Modules\Forums\Models\Thread;

class ThreadDashboardService
{
    public function __construct(
        private readonly ThreadRepositoryInterface $repository,
    ) {}

    public function getAllThreads(User $actor, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $filters['per_page'] = $perPage;
        $search = $filters['search'] ?? null;
        unset($filters['search']);

        if ($actor->hasRole('Instructor') && ! $actor->hasRole('Superadmin')) {
            return $this->repository->getInstructorThreads($actor->id, $filters, $search);
        }

        return $this->repository->getAllThreads($filters, $search);
    }

    public function getMyThreads(User $actor, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $filters['per_page'] = $perPage;
        $search = $filters['search'] ?? null;
        unset($filters['search']);

        return $this->repository->getUserThreads($actor->id, $filters, $search);
    }

    public function getTrendingThreads(User $actor, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $filters['per_page'] = $perPage;
        $search = $filters['search'] ?? null;
        unset($filters['search']);

        if ($actor->hasRole('Instructor') && ! $actor->hasRole('Superadmin')) {
            return $this->repository->getInstructorTrendingThreads($actor->id, $filters, $search);
        }

        return $this->repository->getTrendingThreads($filters, $search);
    }

    public function getWithIncludes(Thread $thread, array $includes = ['author', 'author.media', 'course', 'media', 'tags', 'topLevelReplies', 'topLevelReplies.author', 'topLevelReplies.author.media', 'topLevelReplies.media']): Thread
    {
        return Thread::with($includes)->where('id', $thread->id)->firstOrFail();
    }
}
