<?php

namespace Modules\Content\Services;

use App\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Content\Contracts\Repositories\NewsRepositoryInterface;
use Modules\Content\Contracts\Services\NewsServiceInterface;
use Modules\Content\DTOs\CreateNewsDTO;
use Modules\Content\DTOs\UpdateNewsDTO;
use Modules\Content\Events\NewsPublished;
use Modules\Content\Models\News;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class NewsService implements NewsServiceInterface
{
    public function __construct(
        private NewsRepositoryInterface $repository
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? $perPage;
        $perPage = max(1, min($perPage, 100));
        $page = request()->get('page', 1);
        $searchQuery = request('search');
        $sort = request('sort', '-published_at');
        
        return cache()->tags(['content', 'news'])->remember(
            "content:news:feed:{$perPage}:{$page}:{$searchQuery}:{$sort}:" . md5(json_encode($filters)),
            300,
            function () use ($perPage, $searchQuery) {
                $builder = QueryBuilder::for(News::class);

                if ($searchQuery && trim($searchQuery) !== '') {
                    $builder->search($searchQuery);
                }

                return $builder->allowedFilters([
                    AllowedFilter::exact('status'),
                    AllowedFilter::exact('category'),
                    AllowedFilter::callback('search', fn ($q, $v) => $q->search($v)),
                ])
                    ->allowedIncludes(['author'])
                    ->allowedSorts(['published_at', 'views_count', 'created_at'])
                    ->defaultSort('-published_at')
                    ->paginate($perPage);
            }
        );
    }

    public function search(string $query, array $filters = []): LengthAwarePaginator
    {
        request()->merge(['filter' => array_merge(request('filter', []), ['search' => $query])]);

        return $this->getFeed($filters);
    }

    public function find(int $id): ?News
    {
        return $this->repository->findWithRelations($id);
    }

    public function findBySlug(string $slug): ?News
    {
        return $this->repository->findBySlugWithRelations($slug);
    }

    public function create(CreateNewsDTO $dto, User $author): News
    {
        return DB::transaction(function () use ($dto, $author) {
            $data = array_merge($dto->toArray(), [
                'author_id' => $author->id,
            ]);

            $news = $this->repository->create($data);
            cache()->tags(['content', 'news'])->flush();
            return $news;
        });
    }

    public function update(News $news, UpdateNewsDTO $dto, User $editor): News
    {
        return DB::transaction(function () use ($news, $dto, $editor) {
            $news->saveRevision($editor);

            $updated = $this->repository->update($news, $dto->toArrayWithoutNull());
            cache()->tags(['content', 'news'])->flush();
            return $updated;
        });
    }

    public function delete(News $news, User $user): bool
    {
        $result = $this->repository->delete($news, $user->id);
        cache()->tags(['content', 'news'])->flush();
        return $result;
    }

    /**
     * @throws BusinessException
     */
    public function publish(News $news): News
    {
        if ($news->status === 'published') {
            throw new BusinessException(__('messages.news.already_published'));
        }

        return DB::transaction(function () use ($news) {
            $this->repository->update($news, [
                'status' => 'published',
                'published_at' => now(),
                'scheduled_at' => null,
            ]);

            event(new NewsPublished($news->fresh()));

            cache()->tags(['content', 'news'])->flush();
            return $news->fresh();
        });
    }

    /**
     * @throws BusinessException
     */
    public function schedule(News $news, \Carbon\Carbon $publishAt): News
    {
        if ($publishAt->isPast()) {
            throw new BusinessException(__('messages.content.schedule_future_required'));
        }

        $this->repository->update($news, [
            'status' => 'scheduled',
            'scheduled_at' => $publishAt,
        ]);

        cache()->tags(['content', 'news'])->flush();
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
        $news->increment('views_count');
    }
}
