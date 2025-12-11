<?php

namespace Modules\Content\Services;

use Illuminate\Support\Collection;
use Modules\Content\Contracts\Services\ContentStatisticsServiceInterface;
use Modules\Content\Models\Announcement;
use Modules\Content\Models\News;
use Modules\Content\Repositories\ContentStatisticsRepository;

class ContentStatisticsService implements ContentStatisticsServiceInterface
{
    protected ContentStatisticsRepository $statisticsRepository;

    public function __construct(ContentStatisticsRepository $statisticsRepository)
    {
        $this->statisticsRepository = $statisticsRepository;
    }

    public function getAnnouncementStatistics(Announcement $announcement): array
    {
        $viewCount = $announcement->views_count;
        $readCount = $this->statisticsRepository->getReadCount($announcement->id);
        $readRate = $this->statisticsRepository->calculateReadRate($announcement);
        $unreadUsers = $this->statisticsRepository->getUnreadUsers($announcement);

        return [
            'announcement_id' => $announcement->id,
            'title' => $announcement->title,
            'published_at' => $announcement->published_at,
            'views_count' => $viewCount,
            'read_count' => $readCount,
            'read_rate' => round($readRate, 2),
            'unread_count' => $unreadUsers->count(),
            'unread_users' => $unreadUsers,
        ];
    }

    public function getNewsStatistics(News $news): array
    {
        $viewCount = $news->views_count;
        $readCount = $news->reads()->count();
        $trendingScore = $news->getTrendingScore();

        return [
            'news_id' => $news->id,
            'title' => $news->title,
            'published_at' => $news->published_at,
            'views_count' => $viewCount,
            'read_count' => $readCount,
            'trending_score' => round($trendingScore, 2),
            'is_featured' => $news->is_featured,
        ];
    }

    public function getAllAnnouncementStatistics(array $filters = []): Collection
    {
        return $this->statisticsRepository->getAnnouncementStatistics($filters);
    }

    public function getAllNewsStatistics(array $filters = []): Collection
    {
        return $this->statisticsRepository->getNewsStatistics($filters);
    }

    public function calculateReadRate(Announcement $announcement): float
    {
        return $this->statisticsRepository->calculateReadRate($announcement);
    }

    public function getUnreadUsers(Announcement $announcement): Collection
    {
        return $this->statisticsRepository->getUnreadUsers($announcement);
    }

    public function getTrendingNews(int $limit = 10): Collection
    {
        $news = News::published()
            ->where('published_at', '>=', now()->subDays(7))
            ->with(['author', 'categories'])
            ->get();

        return $news->sortByDesc(function ($item) {
            return $item->getTrendingScore();
        })->take($limit)->values();
    }

    public function getMostViewedNews(int $days = 30, int $limit = 10): Collection
    {
        return $this->statisticsRepository->getMostViewedNews($days, $limit);
    }

    public function getDashboardStatistics(): array
    {
        $totalAnnouncements = Announcement::published()->count();
        $totalNews = News::published()->count();
        $totalViews = Announcement::sum('views_count') + News::sum('views_count');

        $recentAnnouncements = Announcement::published()
            ->where('published_at', '>=', now()->subDays(7))
            ->count();

        $recentNews = News::published()
            ->where('published_at', '>=', now()->subDays(7))
            ->count();

        return [
            'total_announcements' => $totalAnnouncements,
            'total_news' => $totalNews,
            'total_views' => $totalViews,
            'recent_announcements' => $recentAnnouncements,
            'recent_news' => $recentNews,
        ];
    }
}
