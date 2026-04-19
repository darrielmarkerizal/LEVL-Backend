<?php

namespace Modules\Content\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use App\Support\Traits\HandlesFiltering;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Content\Contracts\Repositories\NewsRepositoryInterface;
use Modules\Content\Contracts\Services\ContentStatisticsServiceInterface;
use Modules\Content\Models\Announcement;
use Modules\Content\Models\News;


class ContentStatisticsController extends Controller
{
    use ApiResponse;
    use HandlesFiltering;

    public function __construct(
        protected ContentStatisticsServiceInterface $statisticsService,
        protected NewsRepositoryInterface $newsRepository
    ) {}

    
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewStatistics', [Announcement::class]);

        $type = $request->input('filter.type', 'all');
        $params = $this->extractFilterParams($request);

        $data = [];

        if ($type === 'all' || $type === 'announcements') {
            $data['announcements'] = $this->statisticsService->getAllAnnouncementStatistics($params['filter'] ?? []);
        }

        if ($type === 'all' || $type === 'news') {
            $data['news'] = $this->statisticsService->getAllNewsStatistics($params['filter'] ?? []);
        }

        if ($type === 'all') {
            $data['dashboard'] = $this->statisticsService->getDashboardStatistics();
        }

        return $this->success($data);
    }

    
    public function showAnnouncement(int $id): JsonResponse
    {
        $this->authorize('viewStatistics', [Announcement::class]);

        $announcement = Announcement::findOrFail($id);
        $statistics = $this->statisticsService->getAnnouncementStatistics($announcement);

        return $this->success($statistics);
    }

    
    public function showNews(string $slug): JsonResponse
    {
        $this->authorize('viewStatistics', [News::class]);

        $news = $this->newsRepository->findBySlugOrFail($slug);
        $statistics = $this->statisticsService->getNewsStatistics($news);

        return $this->success($statistics);
    }

    
    public function trending(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $trending = $this->statisticsService->getTrendingNews($limit);

        return $this->success($trending);
    }

    
    public function mostViewed(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $limit = $request->input('limit', 10);

        $mostViewed = $this->statisticsService->getMostViewedNews($days, $limit);

        return $this->success($mostViewed);
    }
}
