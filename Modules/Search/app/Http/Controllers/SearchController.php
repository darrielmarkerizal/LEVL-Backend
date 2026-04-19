<?php

namespace Modules\Search\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use App\Support\Traits\HandlesFiltering;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Search\Contracts\Repositories\SearchHistoryRepositoryInterface;
use Modules\Search\Contracts\Services\SearchServiceInterface;


class SearchController extends Controller
{
    use ApiResponse;
    use HandlesFiltering;

    public function __construct(
        private SearchServiceInterface $searchService,
        private SearchHistoryRepositoryInterface $searchHistoryRepository
    ) {}

    
    public function autocomplete(Request $request): JsonResponse
    {
        $query = $request->input('q', '') ?? '';
        $limit = $request->input('limit', 10);

        $suggestions = $this->searchService->getSuggestions($query, $limit);

        return $this->success(data: $suggestions);
    }

    
    public function getSearchHistory(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 20);

        $history = $this->searchHistoryRepository->findByUserId(auth()->id(), $limit);

        return $this->success(data: $history);
    }

    
    public function clearSearchHistory(Request $request): JsonResponse
    {
        
        $this->searchHistoryRepository->deleteByUserId(auth()->id());

        return $this->success(message: __('messages.search.history_cleared'));
    }

    
    public function deleteHistoryItem(int $id): JsonResponse
    {
        $this->searchHistoryRepository->deleteById($id, auth()->id());

        return $this->success(message: __('messages.search.history_deleted'));
    }

    
    public function globalSearch(Request $request): JsonResponse
    {
        $query = $request->input('q', '') ?? '';

        
        $type = $request->input('type') ?? $request->input('filter.type', 'all');

        
        $validTypes = ['courses', 'units', 'lessons', 'users', 'forums', 'all'];
        if (! in_array($type, $validTypes)) {
            return $this->error(
                message: __('messages.search.invalid_type'),
                code: 422
            );
        }

        
        $user = auth('api')->user();
        $restrictedTypes = ['lessons', 'users', 'forums'];

        if (! $user && ($type === 'all' || in_array($type, $restrictedTypes))) {
            
            if ($type === 'all') {
                $type = 'courses'; 
            } elseif (in_array($type, $restrictedTypes)) {
                return $this->error(
                    message: __('messages.auth.unauthenticated'),
                    code: 401
                );
            }
        }

        $results = $this->searchService->globalSearch($query, 5, $user);

        
        if ($user && ! empty(trim($query))) {
            $totalResults = collect($results)->flatten()->count();
            $this->searchService->saveSearchHistory($user, $query, ['type' => $type], $totalResults);
        }

        
        if ($type === 'all') {
            $data = [
                'users' => \Modules\Auth\Http\Resources\UserIndexResource::collection($results['users'] ?? []),
                'courses' => \Modules\Schemes\Http\Resources\CourseIndexResource::collection($results['courses'] ?? []),
                'forums' => \Modules\Forums\Http\Resources\ThreadResource::collection($results['forums'] ?? []),
            ];
        } else {
            $data = $results;
        }

        return $this->success(data: $data);
    }

    
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:1',
            'type' => 'nullable|string|in:courses,units,lessons,users,all',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = $request->input('q');

        
        
        $type = $request->input('type') ?? $request->input('filter.type');

        
        if (! $type || $type === 'all') {
            return $this->globalSearch($request);
        }

        
        $filters = collect($request->input('filter', []))
            ->except(['type'])
            ->toArray();

        
        $filters['per_page'] = $request->input('per_page', 15);

        $sort = [
            'field' => $request->input('sort', 'created_at'),
            'direction' => 'desc',
        ];

        $user = auth('api')->user();

        
        if (! $user && in_array($type, ['lessons', 'users'])) {
            return $this->error(
                message: __('messages.auth.unauthenticated'),
                code: 401
            );
        }

        $result = $this->searchService->search($query, $filters, $sort, $user, $type);

        
        if ($user && ! empty(trim($query))) {
            $this->searchService->saveSearchHistory($user, $query, $filters, $result->total);
        }

        
        return $this->paginateResponse(
            paginator: $result->items,
            message: __('messages.success'),
            additionalMeta: [
                'search' => [
                    'query' => $result->query,
                    'type' => $type,
                    'execution_time' => round($result->executionTime, 4),
                ],
            ]
        );
    }
}
