<?php

namespace Modules\Content\Services;

use App\Contracts\Services\ContentServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Content\Models\Announcement;
use Modules\Content\Models\ContentRevision;
use Modules\Content\Models\News;
use Modules\Content\Repositories\AnnouncementRepository;
use Modules\Content\Repositories\NewsRepository;

class ContentService implements ContentServiceInterface
{
    protected AnnouncementRepository $announcementRepository;

    protected NewsRepository $newsRepository;

    public function __construct(
        AnnouncementRepository $announcementRepository,
        NewsRepository $newsRepository
    ) {
        $this->announcementRepository = $announcementRepository;
        $this->newsRepository = $newsRepository;
    }

    /**
     * @throws \Exception
     */
    public function createAnnouncement(array $data, User $author): Announcement
    {
        if (empty($data['title']) || empty($data['content'])) {
            throw new \Exception('Title and content are required.');
        }

        return DB::transaction(function () use ($data, $author) {
            $announcementData = [
                'author_id' => $author->id,
                'course_id' => $data['course_id'] ?? null,
                'title' => $data['title'],
                'content' => $data['content'],
                'status' => $data['status'] ?? 'draft',
                'target_type' => $data['target_type'] ?? 'all',
                'target_value' => $data['target_value'] ?? null,
                'priority' => $data['priority'] ?? 'normal',
            ];

            return $this->announcementRepository->create($announcementData);
        });
    }

    /**
     * @throws \Exception
     */
    public function createNews(array $data, User $author): News
    {
        if (empty($data['title']) || empty($data['content'])) {
            throw new \Exception('Title and content are required.');
        }

        return DB::transaction(function () use ($data, $author) {
            $newsData = [
                'author_id' => $author->id,
                'title' => $data['title'],
                'slug' => $data['slug'] ?? null,
                'excerpt' => $data['excerpt'] ?? null,
                'content' => $data['content'],
                'status' => $data['status'] ?? 'draft',
                'is_featured' => $data['is_featured'] ?? false,
                'category_ids' => $data['category_ids'] ?? [],
                'tag_ids' => $data['tag_ids'] ?? [],
            ];

            return $this->newsRepository->create($newsData);
        });
    }

    public function updateAnnouncement(Announcement $announcement, array $data, User $editor): Announcement
    {
        return DB::transaction(function () use ($announcement, $data, $editor) {
            $announcement->saveRevision($editor);

            $updateData = Arr::only($data, ['title', 'content', 'target_type', 'target_value', 'priority']);

            return $this->announcementRepository->update($announcement, $updateData);
        });
    }

    public function updateNews(News $news, array $data, User $editor): News
    {
        return DB::transaction(function () use ($news, $data, $editor) {
            $news->saveRevision($editor);

            $updateData = Arr::only($data, ['title', 'content', 'excerpt', 'is_featured', 'category_ids', 'tag_ids']);

            return $this->newsRepository->update($news, $updateData);
        });
    }

    public function publishContent($content): bool
    {
        return DB::transaction(function () use ($content) {
            $content->update([
                'status' => 'published',
                'published_at' => now(),
                'scheduled_at' => null,
            ]);

            if ($content instanceof Announcement) {
                event(new \Modules\Content\Events\AnnouncementPublished($content));
            } elseif ($content instanceof News) {
                event(new \Modules\Content\Events\NewsPublished($content));
            }

            return true;
        });
    }

    public function scheduleContent($content, \Carbon\Carbon $publishAt): bool
    {
        if ($publishAt->isPast()) {
            throw new \Exception('Scheduled time must be in the future.');
        }

        return $content->update([
            'status' => 'scheduled',
            'scheduled_at' => $publishAt,
        ]);
    }

    public function cancelSchedule($content): bool
    {
        return $content->update([
            'status' => 'draft',
            'scheduled_at' => null,
        ]);
    }

    public function deleteContent($content, User $user): bool
    {
        if ($content instanceof Announcement) {
            return $this->announcementRepository->delete($content, $user->id);
        } elseif ($content instanceof News) {
            return $this->newsRepository->delete($content, $user->id);
        }

        return false;
    }

    public function getAnnouncementsForUser(User $user, array $filters = []): LengthAwarePaginator
    {
        return $this->announcementRepository->getAnnouncementsForUser($user, $filters);
    }

    public function getNewsFeed(array $filters = []): LengthAwarePaginator
    {
        return $this->newsRepository->getNewsFeed($filters);
    }

    public function searchContent(string $query, string $type = 'all', array $filters = []): LengthAwarePaginator
    {
        if ($type === 'news' || $type === 'all') {
            return $this->newsRepository->searchNews($query, $filters);
        }

        return $this->newsRepository->searchNews($query, $filters);
    }

    public function markAsRead($content, User $user): void
    {
        if ($content instanceof Announcement) {
            $content->markAsReadBy($user);
        } elseif ($content instanceof News) {
            $content->reads()->firstOrCreate([
                'user_id' => $user->id,
                'readable_type' => News::class,
                'readable_id' => $content->id,
            ]);
        }
    }

    public function incrementViews($content): void
    {
        if ($content instanceof News) {
            $content->incrementViews();
        } elseif ($content instanceof Announcement) {
            $content->increment('views_count');
        }
    }

    public function getTrendingNews(int $limit = 10): Collection
    {
        return $this->newsRepository->getTrendingNews($limit);
    }

    public function getFeaturedNews(int $limit = 5): Collection
    {
        return $this->newsRepository->getFeaturedNews($limit);
    }
}
