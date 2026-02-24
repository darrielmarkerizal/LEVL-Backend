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
        // If specific ID is provided, delete that entry
        if ($request->has('id')) {
            $this->searchHistoryRepository->deleteById($request->input('id'), auth()->id());

            return $this->success(message: __('messages.search.history_deleted'));
        }

        // Otherwise, clear all history for the user
        $this->searchHistoryRepository->deleteByUserId(auth()->id());

        return $this->success(message: __('messages.search.history_cleared'));
    }

    /**
     * Pencarian Global
     *
     * Mencari data secara global pada User, Course, dan Forum dengan limit 5 per kategori.
     *
     * @summary Pencarian Global
     *
     * @queryParam q string Kata kunci pencarian. Example: Laravel
     *
     * @response 200 scenario="Success" {"success":true,"data":{"users":[],"courses":[],"forums":[]}}
     * @response 401 scenario="Unauthorized" {"success":false,"message":"Tidak terotorisasi."}
     *
     */
    public function globalSearch(Request $request): JsonResponse
    {
        $query = $request->input('q', '') ?? '';
        
        $results = $this->searchService->globalSearch($query, 5);

        // Save search history for authenticated users
        if (auth()->check() && ! empty(trim($query))) {
            $totalResults = $results['users']->count() + $results['courses']->count() + $results['forums']->count();
            $this->searchService->saveSearchHistory(auth()->user(), $query, [], $totalResults);
        }

        $data = [
            'users' => \Modules\Auth\Http\Resources\UserIndexResource::collection($results['users']),
            'courses' => \Modules\Schemes\Http\Resources\CourseIndexResource::collection($results['courses']),
            'forums' => \Modules\Forums\Http\Resources\ThreadResource::collection($results['forums']),
        ];

        return $this->success(data: $data);
    }
}
