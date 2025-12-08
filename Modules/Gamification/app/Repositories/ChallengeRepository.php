<?php

namespace Modules\Gamification\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Gamification\Contracts\Repositories\ChallengeRepositoryInterface;
use Modules\Gamification\Models\Challenge;

class ChallengeRepository implements ChallengeRepositoryInterface
{
    public function findById(int $id): ?Challenge
    {
        return Challenge::find($id);
    }

    public function findActive(): Collection
    {
        return Challenge::active()->get();
    }

    public function findDaily(): Collection
    {
        return Challenge::daily()->active()->get();
    }

    public function findWeekly(): Collection
    {
        return Challenge::weekly()->active()->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Challenge::active()
            ->with('badge')
            ->orderBy('type')
            ->orderBy('points_reward', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): Challenge
    {
        return Challenge::create($data);
    }

    public function update(Challenge $challenge, array $data): Challenge
    {
        $challenge->update($data);

        return $challenge->fresh();
    }

    public function delete(Challenge $challenge): bool
    {
        return $challenge->delete();
    }
}
