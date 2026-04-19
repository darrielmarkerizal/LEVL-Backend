<?php

namespace Modules\Content\Http\Controllers;

use App\Contracts\Services\ContentServiceInterface;
use App\Exceptions\ResourceNotFoundException;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use App\Support\Traits\HandlesFiltering;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Content\Contracts\Services\ContentStatisticsServiceInterface;
use Modules\Content\Contracts\Services\NewsServiceInterface;
use Modules\Content\Http\Requests\CreateNewsRequest;
use Modules\Content\Http\Requests\ScheduleContentRequest;
use Modules\Content\Http\Requests\UpdateContentRequest;
use Modules\Content\Http\Resources\NewsResource;
use Modules\Content\Models\News;


class NewsController extends Controller
{
    use ApiResponse;
    use HandlesFiltering;

    public function __construct(
        private ContentServiceInterface $contentService,
        private ContentStatisticsServiceInterface $statisticsService,
        private NewsServiceInterface $newsService,
    ) {}

    
    public function index(Request $request): JsonResponse
    {
        $params = $this->extractFilterParams($request);

        
        if ($request->has('filter.featured')) {
            $params['filter']['featured'] = $request->boolean('filter.featured');
        }

        $filters = array_merge($params['filter'], [
            'per_page' => $params['per_page'],
            'sort' => $params['sort'] ?? null,
        ]);

        $news = $this->contentService->getNewsFeed($filters);

        return $this->paginateResponse($news);
    }

    
    public function store(CreateNewsRequest $request): JsonResponse
    {
        $this->authorize('create', News::class);
        $news = $this->contentService->createNews($request->validated(), auth()->user());

        return $this->created(NewsResource::make($news), __('messages.news.created'));
    }

    
    public function show(string $slug): JsonResponse
    {
        $news = $this->newsService->findBySlug($slug);

        if (! $news) {
            throw new ResourceNotFoundException(__('messages.news.not_found'));
        }

        $this->contentService->incrementViews($news);

        return $this->success(NewsResource::make($news));
    }

    
    public function update(UpdateContentRequest $request, string $slug): JsonResponse
    {
        $news = $this->newsService->findBySlug($slug);

        if (! $news) {
            throw new ResourceNotFoundException(__('messages.news.not_found'));
        }

        $this->authorize('update', $news);

        $news = $this->contentService->updateNews($news, $request->validated(), auth()->user());

        return $this->success(NewsResource::make($news), __('messages.news.updated'));
    }

    
    public function destroy(string $slug): JsonResponse
    {
        $news = $this->newsService->findBySlug($slug);

        if (! $news) {
            throw new ResourceNotFoundException(__('messages.news.not_found'));
        }

        $this->authorize('delete', $news);

        $this->contentService->deleteContent($news, auth()->user());

        return $this->success(null, __('messages.news.deleted'));
    }

    
    public function publish(string $slug): JsonResponse
    {
        $news = $this->newsService->findBySlug($slug);

        if (! $news) {
            throw new ResourceNotFoundException(__('messages.news.not_found'));
        }

        $this->authorize('publish', $news);

        $this->contentService->publishContent($news);

        return $this->success($news->fresh(), __('messages.news.published'));
    }

    
    public function schedule(ScheduleContentRequest $request, string $slug): JsonResponse
    {
        $news = $this->newsService->findBySlug($slug);

        if (! $news) {
            throw new ResourceNotFoundException(__('messages.news.not_found'));
        }

        $this->authorize('schedule', $news);

        $this->contentService->scheduleContent(
            $news,
            \Carbon\Carbon::parse($request->input('scheduled_at')),
        );

        return $this->success($news->fresh(), __('messages.news.scheduled'));
    }

    
    public function trending(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $trending = $this->statisticsService->getTrendingNews($limit);

        return $this->success($trending);
    }
}
