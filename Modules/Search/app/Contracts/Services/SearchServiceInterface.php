<?php

namespace Modules\Search\Contracts\Services;

use Modules\Auth\Models\User;
use Modules\Search\DTOs\SearchResultDTO;

interface SearchServiceInterface
{
    
    public function search(string $query, array $filters = [], array $sort = []): SearchResultDTO;

    
    public function getSuggestions(string $query, int $limit = 10): array;

    
    
    public function saveSearchHistory(User $user, string $query, array $filters = [], int $resultsCount = 0): void;

    
    public function globalSearch(string $query, int $limitPerCategory = 5): array;
}
