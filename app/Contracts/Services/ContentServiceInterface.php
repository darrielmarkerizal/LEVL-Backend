<?php

namespace App\Contracts\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Auth\Models\User;
use Modules\Content\Models\Announcement;
use Modules\Content\Models\News;

interface ContentServiceInterface
{
    
    public function createAnnouncement(\Modules\Content\DTOs\CreateAnnouncementDTO|array $data, User $author): Announcement;

    
    public function createNews(\Modules\Content\DTOs\CreateNewsDTO|array $data, User $author): News;

    
    public function updateAnnouncement(Announcement $announcement, \Modules\Content\DTOs\UpdateAnnouncementDTO|array $data, User $editor): Announcement;

    
    public function updateNews(News $news, \Modules\Content\DTOs\UpdateNewsDTO|array $data, User $editor): News;

    
    public function publishContent($content): bool;

    
    public function scheduleContent($content, \Carbon\Carbon $publishAt): bool;

    
    public function cancelSchedule($content): bool;

    
    public function deleteContent($content, User $user): bool;

    
    public function getAnnouncementsForUser(User $user, array $filters = []): LengthAwarePaginator;

    
    public function getNewsFeed(array $filters = []): LengthAwarePaginator;

    
    public function searchContent(string $query, string $type = 'all', array $filters = []): LengthAwarePaginator;

    
    public function markAsRead($content, User $user): void;

    
    public function incrementViews($content): void;

    
    public function getTrendingNews(int $limit = 10): Collection;

    
    public function getFeaturedNews(int $limit = 5): Collection;
}
