<?php

declare(strict_types=1);

namespace Modules\Notifications\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Notifications\Models\Post;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PostRepository extends BaseRepository
{
    private const CACHE_TTL = 3600;
    private const CACHE_PREFIX = 'posts:';

    protected array $allowedFilters = ['status', 'category', 'is_pinned'];
    protected array $allowedSorts = ['id', 'created_at', 'updated_at', 'published_at', 'scheduled_at', 'title'];
    protected string $defaultSort = '-created_at';
    protected array $with = ['author', 'audiences'];

    protected function model(): string
    {
        return Post::class;
    }

    public function paginateWithSearch(
        int $perPage = 15,
        ?string $search = null,
        ?string $role = null
    ): LengthAwarePaginator {
        $perPage = max(1, min($perPage, 100));

        if ($search !== null) {
            return $this->buildQuery($search, $role)->paginate($perPage);
        }

        $filters = request()->get('filter', []);
        $page = request()->get('page', 1);
        $sort = request()->get('sort', $this->defaultSort);
        $cacheKey = $this->buildCacheKey('list', $filters, $role, $page, $sort);

        return Cache::tags(['posts'])->remember(
            $cacheKey,
            self::CACHE_TTL,
            fn () => $this->buildQuery($search, $role)->paginate($perPage)
        );
    }

    public function findByUuid(string $uuid): ?Post
    {
        return $this->model()::query()
            ->with(['author', 'lastEditor', 'audiences', 'notifications', 'views'])
            ->where('uuid', $uuid)
            ->first();
    }

    public function getPinnedPosts(?string $role = null): Collection
    {
        $cacheKey = $this->buildCacheKey('pinned', [], $role);

        return Cache::tags(['posts'])->remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($role) {
                $query = $this->model()::query()
                    ->published()
                    ->pinned()
                    ->with(['author', 'audiences'])
                    ->withCount('views');

                if ($role !== null) {
                    $query->forRole($role);
                }

                return $query->orderBy('created_at', 'desc')->get();
            }
        );
    }

    public function getTrashedPosts(int $perPage = 15): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));

        return $this->model()::query()
            ->onlyTrashed()
            ->with(['author', 'lastEditor'])
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);
    }

    public function getPendingScheduledPosts(): Collection
    {
        return $this->model()::query()
            ->pendingPublish()
            ->with(['author', 'audiences', 'notifications'])
            ->get();
    }

    public function getScheduledPosts(?string $role = null): LengthAwarePaginator
    {
        $query = $this->model()::query()
            ->scheduled()
            ->with(['author', 'audiences'])
            ->withCount('views');

        if ($role !== null) {
            $query->forRole($role);
        }

        return $query->orderBy('scheduled_at', 'asc')->paginate(15);
    }

    public function create(array $data): Post
    {
        $post = parent::create($data);
        $this->clearCache();

        return $post;
    }

    public function update($model, array $data): Post
    {
        $updated = parent::update($model, $data);
        $this->clearCache();

        return $updated;
    }

    public function delete($model): bool
    {
        $deleted = parent::delete($model);

        if ($deleted) {
            $this->clearCache();
        }

        return $deleted;
    }

    public function restore(Post $post): bool
    {
        $restored = $post->restore();

        if ($restored) {
            $this->clearCache();
        }

        return $restored;
    }

    public function forceDelete(Post $post): bool
    {
        $deleted = $post->forceDelete();

        if ($deleted) {
            $this->clearCache();
        }

        return $deleted;
    }

    private function buildQuery(
        ?string $search = null,
        ?string $role = null
    ): QueryBuilder {
        $query = $this->model()::query()
            ->with(['author', 'audiences'])
            ->withCount('views');

        if ($search !== null) {
            $query->search($search);
        }

        if ($role !== null) {
            $query->forRole($role);
        }

        return QueryBuilder::for($query)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('category'),
                AllowedFilter::exact('is_pinned'),
            ])
            ->allowedSorts($this->allowedSorts)
            ->defaultSort($this->defaultSort);
    }

    private function buildCacheKey(
        string $type,
        array $filters = [],
        ?string $role = null,
        int $page = 1,
        string $sort = '-created_at'
    ): string {
        $parts = [
            self::CACHE_PREFIX,
            $type,
            http_build_query($filters) ?: 'no-filter',
            $role ?? 'all',
            "page:{$page}",
            "sort:{$sort}",
        ];

        return implode(':', $parts);
    }

    private function clearCache(): void
    {
        Cache::tags(['posts'])->flush();
    }
}