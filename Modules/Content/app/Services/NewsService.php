<?php

namespace Modules\Content\Services;

use App\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Content\Contracts\Repositories\NewsRepositoryInterface;
use Modules\Content\DTOs\CreateNewsDTO;
use Modules\Content\DTOs\UpdateNewsDTO;
use Modules\Content\Events\NewsPublished;
use Modules\Content\Models\ContentRevision;
use Modules\Content\Models\News;

class NewsService
{
    public function __construct(
        private NewsRepositoryInterface $repository
    ) {}

    public function getFeed(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getNewsFeed($filters);
    }

    public function search(string $query, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->searchNews($query, $filters);
    }

    public function findBySlug(string $slug): ?News
    {
        return $this->repository->findBySlugWithRelations($slug);
    }

    public function find(int $id): ?News
    {
        return $this->repository->findWithRelations($id);
    }

    public function create(CreateNewsDTO $dto, User $author): News
    {
        return DB::transaction(function () use ($dto, $author) {
            $data = array_merge($dto->toArray(), [
                'author_id' => $author->id,
            ]);

            return $this->repository->create($data);
        });
    }

    public function update(News $news, UpdateNewsDTO $dto, User $editor): News
    {
        return DB::transaction(function () use ($news, $dto, $editor) {
            $this->saveRevision($news, $editor);

            return $this->repository->update($news, $dto->toArrayWithoutNull());
        });
    }

    public function delete(News $news, User $user): bool
    {
        return $this->repository->delete($news, $user->id);
    }

    /**
     * @throws BusinessException
     */
    public function publish(News $news): News
    {
        if ($news->status === 'published') {
            throw new BusinessException('News sudah dipublikasikan.');
        }

        return DB::transaction(function () use ($news) {
            $this->repository->update($news, [
                'status' => 'published',
                'published_at' => now(),
                'scheduled_at' => null,
            ]);

            event(new NewsPublished($news->fresh()));

            return $news->fresh();
        });
    }

    /**
     * @throws BusinessException
     */
    public function schedule(News $news, \Carbon\Carbon $publishAt): News
    {
        if ($publishAt->isPast()) {
            throw new BusinessException('Waktu jadwal harus di masa depan.');
        }

        $this->repository->update($news, [
            'status' => 'scheduled',
            'scheduled_at' => $publishAt,
        ]);

        return $news->fresh();
    }

    public function getTrending(int $limit = 10): Collection
    {
        return $this->repository->getTrendingNews($limit);
    }

    public function getFeatured(int $limit = 5): Collection
    {
        return $this->repository->getFeaturedNews($limit);
    }

    public function getScheduledForPublishing(): Collection
    {
        return $this->repository->getScheduledForPublishing();
    }

    public function incrementViews(News $news): void
    {
        $news->incrementViews();
    }

    private function saveRevision(News $news, User $editor): void
    {
        ContentRevision::create([
            'content_type' => News::class,
            'content_id' => $news->id,
            'editor_id' => $editor->id,
            'title' => $news->title,
            'content' => $news->content,
        ]);
    }
}
