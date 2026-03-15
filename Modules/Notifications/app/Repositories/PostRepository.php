<?php

declare(strict_types=1);

namespace Modules\Notifications\app\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Notifications\app\Models\Post;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PostRepository extends BaseRepository
{
    private const CACHE_TTL = 3600; // 1 hour
    private const CACHE_PREFIX = 'posts:';

    protected array $allowedFilters = ['status', 'category', 'is_pinned'];
    protected array $allowedSorts = ['id', 'created_at', 'updated_at', 'published_at', 'scheduled_at', 'title'];
    protected string $defaultSort = '-created_at';
    protected array $with = ['author', 'audiences', 'views'];

    protected function model(): string
    {
        return Post::class;
    }

    /**
     * Paginate posts with filters for status, category, search, and role
     */
    public function paginate(
        int $perPage = 15,
        ?string $status = null,
        ?string $category = null,
        ?string $search = null,
        ?string $role = null
    ): LengthAwarePaginator {
        $perPage = max(1, min($perPage, 100));

        // Don't cache when search is provided
        if ($search !== null) {
            return $this->buildQuery($status, $category, $search, $role)
                ->paginate($perPage);
        }

        $page = request()->get('page', 1);
        $cacheKey = $this->buildCacheKey('list', $status, $category, $role, $page);

        return Cache::tags(['posts'])->remember(
            $cacheKey,
            self::CACHE_TTL,
            fn () => $this->buildQuery($status, $category, $search, $role)
                ->paginate($perPage)
        );
    }

    /**
     * Find post by UUID
     */
    public function findByUuid(string $uuid): ?Post
    {
        return $this->model()::query()
            ->with(['author', 'lastEditor', 'audiences', 'notifications', 'views'])
            ->where('uuid', $uuid)
            ->first();
    }

    /**
     * Get pinned posts with optional role filtering
     */
    public function getPinnedPosts(?string $role = null): Collection
    {
        $cacheKey = $this->buildCacheKey('pinned', null, null, $role);

        return Cache::tags(['posts'])->remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($role) {
                $query = $this->model()::query()
                    ->published()
                    ->pinned()
                    ->with(['author', 'audiences', 'views']);

                if ($role !== null) {
                    $query->forRole($role);
                }

                return $query->orderBy('created_at', 'desc')->get();
            }
        );
    }

    /**
     * Get trashed posts with pagination
     */
    public function getTrashedPosts(int $perPage = 15): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));

        return $this->model()::query()
            ->onlyTrashed()
            ->with(['author', 'lastEditor'])
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get pending scheduled posts (ready to be published)
     */
    public function getPendingScheduledPosts(): Collection
    {
        return $this->model()::query()
            ->pendingPublish()
            ->with(['author', 'audiences', 'notifications'])
            ->get();
    }

    /**
     * Get scheduled posts with optional role filtering
     */
    public function getScheduledPosts(?string $role = null): LengthAwarePaginator
    {
        $query = $this->model()::query()
            ->scheduled()
            ->with(['author', 'audiences']);

        if ($role !== null) {
            $query->forRole($role);
        }

        return $query->orderBy('scheduled_at', 'asc')->paginate(15);
    }

    /**
     * Create a new post
     */
    public function create(array $data): Post
    {
        $post = parent::create($data);
        $this->clearCache();

        return $post;
    }

    /**
     * Update an existing post
     */
    public function update($model, array $data): Post
    {
        $updated = parent::update($model, $data);
        $this->clearCache();

        return $updated;
    }

    /**
     * Soft delete a post
     */
    public function delete($model): bool
    {
        $deleted = parent::delete($model);
        
        if ($deleted) {
            $this->clearCache();
        }

        return $deleted;
    }

    /**
     * Restore a soft-deleted post
     */
    public function restore(Post $post): bool
    {
        $restored = $post->restore();
        
        if ($restored) {
            $this->clearCache();
        }

        return $restored;
    }

    /**
     * Permanently delete a post
     */
    public function forceDelete(Post $post): bool
    {
        $deleted = $post->forceDelete();
        
        if ($deleted) {
            $this->clearCache();
        }

        return $deleted;
    }

    /**
     * Build query with filters using Spatie Query Builder
     */
    private function buildQuery(
        ?string $status = null,
        ?string $category = null,
        ?string $search = null,
        ?string $role = null
    ): QueryBuilder {
        $query = $this->model()::query()
            ->with(['author', 'audiences', 'views']);

        // Apply search using PgSearchable trait
        if ($search !== null) {
            $query->search($search);
        }

        // Apply status filter
        if ($status !== null) {
            $query->where('status', $status);
        }

        // Apply category filter
        if ($category !== null) {
            $query->where('category', $category);
        }

        // Apply role filter
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

    /**
     * Build cache key from parameters
     */
    private function buildCacheKey(
        string $type,
        ?string $status = null,
        ?string $category = null,
        ?string $role = null,
        int $page = 1
    ): string {
        $parts = [
            self::CACHE_PREFIX,
            $type,
            $status ?? 'all',
            $category ?? 'all',
            $role ?? 'all',
            "page:{$page}"
        ];

        return implode(':', $parts);
    }

    /**
     * Clear all post caches
     */
    private function clearCache(): void
    {
        Cache::tags(['posts'])->flush();
    }
}

