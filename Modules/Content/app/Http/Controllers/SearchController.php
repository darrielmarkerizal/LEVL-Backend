<?php

namespace Modules\Content\Http\Controllers;

use App\Contracts\Services\ContentServiceInterface;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use App\Support\Traits\HandlesFiltering;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class SearchController extends Controller
{
    use ApiResponse;
    use HandlesFiltering;

    public function __construct(
        protected ContentServiceInterface $contentService
    ) {}

    
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'required|string|min:2',
            'filter.type' => 'nullable|in:all,news,announcements',
        ]);

        $query = $request->input('search');
        $type = $request->input('filter.type', 'all');

        $params = $this->extractFilterParams($request);

        $results = $this->contentService->searchContent($query, $type, $params);

        return $this->success($results);
    }
}
