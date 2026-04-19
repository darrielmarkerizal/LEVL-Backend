<?php

namespace Modules\Search\Contracts\Repositories;

use Illuminate\Support\Collection;
use Modules\Search\Models\SearchHistory;

interface SearchHistoryRepositoryInterface
{
    
    public function findByUserId(int $userId, int $limit = 20): Collection;

    
    public function create(array $data): SearchHistory;

    
    public function getLastSearchByUser(int $userId): ?SearchHistory;

    
    public function deleteById(int $id, int $userId): int;

    
    public function deleteByUserId(int $userId): int;

    
    public function update(SearchHistory $history, array $data): bool;
}
