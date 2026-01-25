<?php

namespace Modules\Content\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Modules\Content\Contracts\Repositories\NewsRepositoryInterface;
use Modules\Content\Models\News;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class NewsRepository implements NewsRepositoryInterface
{
    public function getNewsFeed(array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);
        $request = new Request(['filter' => $filters]);

        return QueryBuilder::for(News::published()->class, $request)
            ->with(['author', 'categories', 'tags'])
            ->withCount('reads')
            ->allowedFilters([
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('tag_id'),
                AllowedFilter::exact('featured'),
                AllowedFilter::callback('date_from', fn ($q, $v) => $q->where('published_at', '>=', $v)),
                AllowedFilter::callback('date_to', fn ($q, $v) => $q->where('published_at', '<=', $v)),
            ])
            ->allowedSorts(['published_at', 'views_count', 'created_at'])
            ->defaultSort('-published_at')
            ->paginate($perPage)
            ->appends($filters);
    }

    public function searchNews(string $searchQuery, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        return News::published()
            ->search($searchQuery)
            ->with(['author', 'categories', 'tags'])
            ->withCount('reads')
            ->query(function ($q) use ($filters) {
                if (!empty($filters['category_id'])) {
                    $q->whereHas('categories', fn ($q2) => $q2->where('content_categories.id', (int) $filters['category_id']));
                }
            })
            ->orderBy('is_featured', 'desc')
            ->orderBy('published_at', 'desc')
            ->paginate($perPage)
            ->appends($filters);
    }

    public function findBySlugWithRelations(string $slug): ?News
    {
        return News::where('slug', $slug)
            ->with(['author', 'categories', 'tags', 'revisions.editor'])
            ->withCount('reads')
            ->first();
    }

    public function findWithRelations(int $newsId): ?News
    {
        return News::with(['author', 'categories', 'tags', 'revisions.editor'])
            ->withCount('reads')
            ->find($newsId);
    }

    public function create(array $data): News
    {
        $news = News::create($data);

        if (isset($data['category_ids'])) {
            $news->categories()->sync($data['category_ids']);
        }

        if (isset($data['tag_ids'])) {
            $news->tags()->sync($data['tag_ids']);
        }

        return $news->fresh(['categories', 'tags']);
    }

    public function update(News $news, array $data): News
    {
        $news->update($data);

        if (isset($data['category_ids'])) {
            $news->categories()->sync($data['category_ids']);
        }

        if (isset($data['tag_ids'])) {
            $news->tags()->sync($data['tag_ids']);
        }

        return $news->fresh(['categories', 'tags']);
    }

    public function delete(News $news, ?int $deletedBy = null): bool
    {
        if ($deletedBy) {
            $news->deleted_by = $deletedBy;
            $news->save();
        }

        return $news->delete();
    }

    public function getTrendingNews(int $limit = 10): Collection
    {
        return News::published()
            ->where('published_at', '>=', now()->subDays(7))
            ->with(['author', 'categories'])
            ->orderByRaw('views_count / (TIMESTAMPDIFF(HOUR, published_at, NOW()) + 1) DESC')
            ->limit($limit)
            ->get();
    }

    public function getFeaturedNews(int $limit = 5): Collection
    {
        return News::published()
            ->featured()
            ->with(['author', 'categories'])
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getScheduledForPublishing(): Collection
    {
        return News::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();
    }

    public function findBySlugOrFail(string $slug): News
    {
        return News::where('slug', $slug)->firstOrFail();
    }
}
