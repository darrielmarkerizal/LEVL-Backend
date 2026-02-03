<?php

namespace Modules\Forums\Contracts\Repositories;

use Modules\Forums\Models\Reaction;

interface ReactionRepositoryInterface
{
     
    public function findByUserAndReactable(int $userId, string $reactableType, int $reactableId): ?Reaction;

     
    public function create(array $data): Reaction;

     
    public function delete(Reaction $reaction): bool;
}
