<?php

declare(strict_types=1);

namespace Modules\Schemes\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Str;
use Modules\Schemes\Contracts\Repositories\TagRepositoryInterface;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Tag;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TagService
{
    use \App\Support\Traits\BuildsQueryBuilderRequest;

    public function __construct(
        private readonly TagRepositoryInterface $repository
    ) {}

    public function list(array $filters = [], int $perPage = 0): LengthAwarePaginator|Collection
    {
        $key = "schemes:tags:list:{$perPage}:".md5(json_encode($filters));
        if ($perPage > 0) {
            $key .= ':'.request('page', 1);
        }

        return cache()->tags(['schemes', 'tags'])->remember($key, 300, function () use ($filters, $perPage) {
            $query = $this->buildQuery($filters);

            if ($perPage > 0) {
                return $query->paginate($perPage);
            }

            return $query->get();
        });
    }

    private function buildQuery(array $filters = []): QueryBuilder
    {
        $searchQuery = data_get($filters, 'search');

        $cleanFilters = \Illuminate\Support\Arr::except($filters, ['search']);

        $builder = QueryBuilder::for(Tag::class, $this->buildQueryBuilderRequest($cleanFilters));

        if ($searchQuery && trim((string) $searchQuery) !== '') {
            $builder->search($searchQuery);
        }

        return $builder
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::partial('slug'),
                AllowedFilter::partial('description'),
            ])
            ->allowedSorts(['name', 'slug', 'created_at', 'updated_at'])
            ->defaultSort('name');
    }

    public function create(array $data): Tag
    {
        $name = trim((string) ($data['name'] ?? ''));

        $tag = $this->firstOrCreateByName($name);
        cache()->tags(['schemes', 'tags'])->flush();

        return $tag;
    }

    public function handleCreate(array $data): BaseCollection|Tag
    {
        if (array_key_exists(0, $data)) {
            $names = collect($data)->pluck('name')->toArray();

            return $this->createMany($names);
        }

        if (isset($data['names']) && is_array($data['names'])) {
            return $this->createMany($data['names']);
        }

        return $this->create($data);
    }

    public function createMany(array $names): BaseCollection
    {
        return BaseCollection::make($names)
            ->map(fn ($name) => trim((string) $name))
            ->filter(fn ($name) => $name !== '')
            ->map(fn ($name) => $this->firstOrCreateByName($name))
            ->values();
    }

    public function update(int $id, array $data): ?Tag
    {
        $tag = $this->repository->findById($id);
        if (! $tag) {
            return null;
        }

        $payload = $this->preparePayload($data, $tag->id, $tag->slug, $tag->name);
        $tag->fill($payload);
        $tag->save();

        cache()->tags(['schemes', 'tags'])->flush();

        return $tag;
    }

    public function delete(int $id): bool
    {
        $tag = $this->repository->findById($id);
        if (! $tag) {
            return false;
        }

        $tag->courses()->detach();

        $deleted = (bool) $tag->delete();
        if ($deleted) {
            cache()->tags(['schemes', 'tags'])->flush();
            cache()->tags(['schemes', 'courses'])->flush();
        }

        return $deleted;
    }

    public function syncCourseTags(Course $course, array $tags): void
    {
        $tagIds = $this->resolveTagIds($tags);

        $course->tags()->sync($tagIds);

        \Illuminate\Support\Facades\DB::afterCommit(function (): void {
            cache()->tags(['schemes', 'tags'])->flush();
            cache()->tags(['schemes', 'courses'])->flush();
        });
    }

    private function preparePayload(array $data, ?int $ignoreId = null, ?string $currentSlug = null, ?string $currentName = null): array
    {
        $name = trim((string) ($data['name'] ?? ''));

        return [
            'name' => $name,
        ];
    }

    private function firstOrCreateByName(string $name): Tag
    {
        $existing = Tag::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing) {
            return $existing;
        }

        $payload = $this->preparePayload(['name' => $name]);

        try {
            return $this->repository->create($payload);
        } catch (\Illuminate\Database\QueryException $e) {
            // If we hit a unique constraint violation, try to find the tag again
            // This handles race conditions where another process created the tag
            if ($this->isUniqueConstraintViolation($e)) {
                $existing = Tag::query()
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                    ->first();

                if ($existing) {
                    return $existing;
                }
            }

            throw $e;
        }
    }

    private function isUniqueConstraintViolation(\Illuminate\Database\QueryException $e): bool
    {
        $sqlState = is_array($e->errorInfo) ? ($e->errorInfo[0] ?? null) : null;
        $message = strtolower($e->getMessage());

        return $sqlState === '23505'
            || $sqlState === '23000'
            || str_contains($message, 'duplicate key')
            || str_contains($message, 'unique constraint')
            || str_contains($message, 'already exists')
            || str_contains($message, 'duplicate entry');
    }

    private function ensureUniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        return $this->findUniqueSlug($slug, $slug, 1, $ignoreId);
    }

    private function findUniqueSlug(string $base, string $slug, int $counter, ?int $ignoreId): string
    {
        $exists = Tag::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists();

        if (! $exists) {
            return $slug;
        }

        return $this->findUniqueSlug($base, "{$base}-{$counter}", $counter + 1, $ignoreId);
    }

    private function resolveTagIds(array $tags): array
    {
        $numericIds = [];
        $textValues = [];

        foreach ($tags as $tag) {
            if (is_numeric($tag)) {
                $numericIds[] = (int) $tag;

                continue;
            }

            $value = trim((string) $tag);
            if ($value === '') {
                continue;
            }

            $textValues[mb_strtolower($value)] = $value;
        }

        $resolved = [];

        if (! empty($numericIds)) {
            try {
                $resolved = Tag::query()
                    ->whereIn('id', array_unique($numericIds))
                    ->pluck('id')
                    ->all();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to resolve numeric tag IDs', [
                    'ids' => $numericIds,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }

        foreach ($textValues as $lower => $value) {
            $slug = Str::slug($value);

            try {
                $existing = Tag::query()
                    ->where(function ($q) use ($slug, $lower) {
                        $q->whereRaw('LOWER(slug) = ?', [$slug])
                            ->orWhereRaw('LOWER(slug) = ?', [$lower])
                            ->orWhereRaw('LOWER(name) = ?', [$lower]);
                    })
                    ->value('id');

                if ($existing !== null) {
                    $resolved[] = $existing;

                    continue;
                }

                // Use firstOrCreateByName which handles race conditions
                $tag = $this->firstOrCreateByName($value);
                $resolved[] = $tag->getKey();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to resolve or create tag', [
                    'value' => $value,
                    'slug' => $slug,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }

        return array_values(array_unique(array_filter($resolved)));
    }
}
