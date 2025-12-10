<?php

namespace Modules\Content\Contracts\Services;

use Illuminate\Support\Collection;
use Modules\Content\Models\Announcement;
use Modules\Content\Models\News;

interface ContentStatisticsServiceInterface
{
    /**
     * Get statistics for a specific announcement.
     */
    public function getAnnouncementStatistics(Announcement $announcement): array;

    /**
     * Get statistics for a specific news article.
     */
    public function getNewsStatistics(News $news): array;

    /**
     * Get overall statistics for all announcements.
     */
    public function getAllAnnouncementStatistics(array $filters = []): Collection;

    /**
     * Get overall statistics for all news.
     */
    public function getAllNewsStatistics(array $filters = []): Collection;

    /**
     * Calculate read rate for an announcement.
     */
    public function calculateReadRate(Announcement $announcement): float;

    /**
     * Get list of users who haven't read an announcement.
     */
    public function getUnreadUsers(Announcement $announcement): Collection;

    /**
     * Get trending news.
     */
    public function getTrendingNews(int $limit = 10): Collection;

    /**
     * Get most viewed news in a time period.
     */
    public function getMostViewedNews(int $days = 30, int $limit = 10): Collection;

    /**
     * Get dashboard statistics summary.
     */
    public function getDashboardStatistics(): array;
}
