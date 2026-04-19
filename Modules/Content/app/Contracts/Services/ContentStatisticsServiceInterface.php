<?php

namespace Modules\Content\Contracts\Services;

use Illuminate\Support\Collection;
use Modules\Content\Models\Announcement;
use Modules\Content\Models\News;

interface ContentStatisticsServiceInterface
{
    
    public function getAnnouncementStatistics(Announcement $announcement): array;

    
    public function getNewsStatistics(News $news): array;

    
    public function getAllAnnouncementStatistics(array $filters = []): Collection;

    
    public function getAllNewsStatistics(array $filters = []): Collection;

    
    public function calculateReadRate(Announcement $announcement): float;

    
    public function getUnreadUsers(Announcement $announcement): Collection;

    
    public function getTrendingNews(int $limit = 10): Collection;

    
    public function getMostViewedNews(int $days = 30, int $limit = 10): Collection;

    
    public function getDashboardStatistics(): array;
}
