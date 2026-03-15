<?php

namespace Modules\Search\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use App\Support\Traits\HandlesFiltering;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Search\Contracts\Repositories\SearchHistoryRepositoryInterface;
use Modules\Search\Contracts\Services\SearchServiceInterface;

/**
 * @tags Pencarian
 */
class SearchController extends Controller
{
    use ApiResponse;
    use HandlesFiltering;

    public function __construct(
        private SearchServiceInterface $searchService,
        private SearchHistoryRepositoryInterface $searchHistoryRepository
    ) {}

    /**
     * Saran Pencarian
     *
     * Mendapatkan suggestion/autocomplete untuk pencarian kursus.
     *
     * @summary Saran Pencarian
     *
     * @queryParam q string Kata kunci untuk autocomplete. Example: Lar
     * @queryParam limit integer Jumlah maksimal suggestions. Default: 10. Example: 5
     *
     * @response 200 scenario="Success" {"success":true,"data":["Laravel Basics","Laravel Advanced","Laravel API Development","Laravel Testing","Laravel Performance"]}
     * @response 401 scenario="Unauthorized" {"success":false,"message":"Tidak terotorisasi."}
     *
     * @authenticated
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $query = $request->input('q', '') ?? '';
        $limit = $request->input('limit', 10);

        $suggestions = $this->searchService->getSuggestions($query, $limit);

        return $this->success(data: $suggestions);
    }

    /**
     * Riwayat Pencarian
     *
     * Mengambil riwayat pencarian user yang sedang login.
     *
     * @summary Riwayat Pencarian
     *
     * @queryParam limit integer Jumlah maksimal history yang ditampilkan. Default: 20. Example: 10
     *
     * @response 200 scenario="Success" {"success":true,"data":[{"id":1,"user_id":5,"query":"Laravel","filters":{"category_id":[1]},"result_count":25,"created_at":"2025-12-10T10:30:00Z"},{"id":2,"user_id":5,"query":"Vue.js","filters":{},"result_count":10,"created_at":"2025-12-09T15:20:00Z"}]}
     * @response 401 scenario="Unauthorized" {"success":false,"message":"Tidak terotorisasi."}
     *
     * @authenticated
     */
    public function getSearchHistory(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 20);

        $history = $this->searchHistoryRepository->findByUserId(auth()->id(), $limit);

        return $this->success(data: $history);
    }

    /**
     * Hapus Riwayat Pencarian
     *
     * Menghapus riwayat pencarian. Jika `id` diberikan, hapus entry tertentu. Jika tidak, hapus semua riwayat user.
     *
     * @summary Hapus Riwayat Pencarian
     *
     * @queryParam id integer optional ID history tertentu yang akan dihapus. Example: 1
     *
     * @response 200 scenario="Success - Specific Entry" {"success":true,"message":"Search history entry deleted successfully"}
     * @response 200 scenario="Success - All History" {"success":true,"message":"All search history cleared successfully"}
     * @response 401 scenario="Unauthorized" {"success":false,"message":"Tidak terotorisasi."}
     *
     * @authenticated
     */
    public function clearSearchHistory(Request $request): JsonResponse
    {
        // Clear all history for the user
        $this->searchHistoryRepository->deleteByUserId(auth()->id());

        return $this->success(message: __('messages.search.history_cleared'));
    }

    /**
     * Hapus Item Riwayat Pencarian
     *
     * Menghapus satu item riwayat pencarian berdasarkan ID.
     *
     * @summary Hapus Item Riwayat Pencarian
     *
     * @urlParam id integer required ID history yang akan dihapus. Example: 1
     *
     * @response 200 scenario="Success" {"success":true,"message":"Search history entry deleted successfully"}
     * @response 401 scenario="Unauthorized" {"success":false,"message":"Tidak terotorisasi."}
     * @response 404 scenario="Not Found" {"success":false,"message":"Search history not found"}
     *
     * @authenticated
     */
    public function deleteHistoryItem(int $id): JsonResponse
    {
        $this->searchHistoryRepository->deleteById($id, auth()->id());

        return $this->success(message: __('messages.search.history_deleted'));
    }

    /**
     * Pencarian Global
     *
     * Mencari data secara global pada User, Course, dan Forum dengan limit 5 per kategori.
     *
     * @summary Pencarian Global
     *
     * @queryParam q string Kata kunci pencarian. Example: Laravel
     * @queryParam type string Tipe pencarian (courses, units, lessons, users, forums, all). Default: all. Example: courses
     *
     * @response 200 scenario="Success" {"success":true,"data":{"users":[],"courses":[],"forums":[]}}
     * @response 401 scenario="Unauthorized" {"success":false,"message":"Tidak terotorisasi."}
     */
    public function globalSearch(Request $request): JsonResponse
    {
        $query = $request->input('q', '') ?? '';
        
        // Extract type from either direct parameter or filter array
        $type = $request->input('type') ?? $request->input('filter.type', 'all');

        // Validate type
        $validTypes = ['courses', 'units', 'lessons', 'users', 'forums', 'all'];
        if (!in_array($type, $validTypes)) {
            return $this->error(
                message: __('messages.search.invalid_type'),
                code: 422
            );
        }

        // Check authentication for restricted types
        $user = auth('api')->user();
        $restrictedTypes = ['lessons', 'users', 'forums'];
        
        if (!$user && ($type === 'all' || in_array($type, $restrictedTypes))) {
            // For unauthenticated users, only allow courses and units
            if ($type === 'all') {
                $type = 'courses'; // Default to courses for public
            } elseif (in_array($type, $restrictedTypes)) {
                return $this->error(
                    message: __('messages.auth.unauthenticated'),
                    code: 401
                );
            }
        }

        $results = $this->searchService->globalSearch($query, 5, $user);

        // Save search history for authenticated users
        if ($user && !empty(trim($query))) {
            $totalResults = collect($results)->flatten()->count();
            $this->searchService->saveSearchHistory($user, $query, ['type' => $type], $totalResults);
        }

        // Format results based on type
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

    /**
     * Pencarian dengan Filter
     *
     * Mencari data dengan filter dan pagination.
     *
     * @summary Pencarian dengan Filter
     *
     * @queryParam q string required Kata kunci pencarian. Example: Laravel
     * @queryParam type string Tipe pencarian (courses, units, lessons, users). Default: courses. Example: courses
     * @queryParam filter[status] string Filter status. Example: published
     * @queryParam filter[category_id] integer Filter kategori. Example: 1
     * @queryParam filter[level_tag] string Filter level. Example: beginner
     * @queryParam sort string Field untuk sorting. Example: -created_at
     * @queryParam per_page integer Jumlah item per halaman. Default: 15. Example: 20
     *
     * @response 200 scenario="Success" {"success":true,"data":{"data":[],"meta":{"current_page":1,"total":0}}}
     * @response 401 scenario="Unauthorized" {"success":false,"message":"Tidak terotorisasi."}
     *
     * @authenticated
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:1',
            'type' => 'nullable|string|in:courses,units,lessons,users,all',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = $request->input('q');
        
        // Extract type from either direct parameter or filter array
        // Default to 'all' if not specified for better search experience
        $type = $request->input('type') ?? $request->input('filter.type');
        
        // If type is still null or 'all', use globalSearch instead
        if (!$type || $type === 'all') {
            return $this->globalSearch($request);
        }
        
        // Get filters from filter[] parameters, excluding 'type' which is handled separately
        $filters = collect($request->input('filter', []))
            ->except(['type'])
            ->toArray();
        
        // Add per_page separately (not a Spatie filter)
        $filters['per_page'] = $request->input('per_page', 15);
        
        $sort = [
            'field' => $request->input('sort', 'created_at'),
            'direction' => 'desc',
        ];

        $user = auth('api')->user();

        // Check authentication for restricted types
        if (!$user && in_array($type, ['lessons', 'users'])) {
            return $this->error(
                message: __('messages.auth.unauthenticated'),
                code: 401
            );
        }

        $result = $this->searchService->search($query, $filters, $sort, $user, $type);

        // Save search history
        if ($user && !empty(trim($query))) {
            $this->searchService->saveSearchHistory($user, $query, $filters, $result->total);
        }

        // Use paginateResponse from ApiResponse trait
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
