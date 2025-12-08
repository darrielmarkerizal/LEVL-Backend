<?php

namespace Modules\Gamification\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Gamification\Models\Challenge;

interface ChallengeRepositoryInterface
{
    public function findById(int $id): ?Challenge;

    public function findActive(): Collection;

    public function findDaily(): Collection;

    public function findWeekly(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Challenge;

    public function update(Challenge $challenge, array $data): Challenge;

    public function delete(Challenge $challenge): bool;
}
