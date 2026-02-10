<?php

declare(strict_types=1);

namespace Modules\Common\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

use Modules\Common\Contracts\Services\ChallengeManagementServiceInterface;
use Modules\Common\Repositories\ChallengeManagementRepository;
use Modules\Gamification\Models\Challenge;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ChallengeManagementService
implements ChallengeManagementServiceInterface
{
    public function __construct(private readonly ChallengeManagementRepository $repository) {}

    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));
        $page = request()->get('page', 1);
        $search = $params['search'] ?? request('search');
        $sort = request('sort', '-created_at');

        return cache()->tags(['common', 'challenges'])->remember(
            "common:challenges:paginate:{$perPage}:{$page}:{$search}:{$sort}",
            300,
            function () use ($perPage, $search) {
                $query = Challenge::query()->with('badge');

                if ($search && trim($search) !== '') {
                    $query->search($search);
                }

                return QueryBuilder::for($query)
                    ->allowedFilters([
                        AllowedFilter::exact('id'),
                        AllowedFilter::partial('title'),
                        AllowedFilter::exact('type'),
                        AllowedFilter::callback('search', fn ($q, $v) => $q->search($v)),
                    ])
                    ->allowedSorts(['id', 'title', 'type', 'points_reward', 'start_at', 'end_at', 'created_at', 'updated_at'])
                    ->defaultSort('-created_at')
                    ->paginate($perPage);
            }
        );
    }

    public function create(array $data): Challenge
    {
        return DB::transaction(function () use ($data) {
            $challenge = $this->repository->create($data);
            cache()->tags(['common', 'challenges'])->flush();
            return $challenge;
        });
    }

    public function find(int $id): ?Challenge
    {
        return $this->repository->findById($id);
    }

    public function update(int $id, array $data): ?Challenge
    {
        $achievement = $this->repository->findById($id);
        if (! $achievement) {
            return null;
        }
        return DB::transaction(function () use ($achievement, $data) {
            $updated = $this->repository->update($achievement, $data);
            cache()->tags(['common', 'challenges'])->flush();
            return $updated;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $achievement = $this->repository->findById($id);
            if (! $achievement) {
                return false;
            }
            $result = $this->repository->delete($achievement);
            cache()->tags(['common', 'challenges'])->flush();
            return $result;
        });
    }
}
