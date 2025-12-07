<?php

namespace Modules\Schemes\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Modules\Schemes\Contracts\Repositories\CourseRepositoryInterface;
use Modules\Schemes\Models\Course;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CourseRepository implements CourseRepositoryInterface
{
    /**
     * Default relations to load.
     */
    protected array $with = ['tags'];

    public function query(): Builder
    {
        return Course::query()->with($this->with);
    }

    public function findById(int $id): ?Course
    {
        return $this->query()->find($id);
    }

    public function findBySlug(string $slug): ?Course
    {
        return $this->query()->where('slug', $slug)->first();
    }

    /**
     * Paginate courses with Spatie Query Builder + Scout search.
     *
     * Supports:
     * - filter[search] (Meilisearch), filter[status], filter[level_tag], filter[type], filter[category_id], filter[tag]
     * - sort: id, code, title, created_at, updated_at, published_at
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->buildQuery()->paginate($perPage);
    }

    /**
     * List all courses with Spatie Query Builder + Scout search.
     */
    public function list(): Collection
    {
        return $this->buildQuery()->get();
    }

    /**
     * Build query with Spatie Query Builder + Scout search.
     */
    private function buildQuery(): QueryBuilder
    {
        $searchQuery = request('filter.search');
        $tagFilter = request('filter.tag');

        $builder = QueryBuilder::for(Course::class)
            ->with($this->with);

        // Use Scout/Meilisearch for full-text search
        if ($searchQuery && trim($searchQuery) !== '') {
            $ids = Course::search($searchQuery)->keys()->toArray();
            $builder->whereIn('id', $ids);
        }

        // Apply tag filters
        if ($tagFilter) {
            $this->applyTagFilters($builder->getEloquentBuilder(), $tagFilter);
        }

        return $builder
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('level_tag'),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('category_id'),
            ])
            ->allowedSorts(['id', 'code', 'title', 'created_at', 'updated_at', 'published_at'])
            ->defaultSort('title');
    }

    /**
     * Apply tag filters to query.
     */
    private function applyTagFilters(Builder $query, $filterTag): void
    {
        $tags = $this->parseArrayFilter($filterTag);

        if (empty($tags)) {
            return;
        }

        foreach ($tags as $tagValue) {
            $value = trim((string) $tagValue);
            if ($value === '') {
                continue;
            }

            $slug = Str::slug($value);

            $query->whereHas('tags', function (Builder $tagQuery) use ($value, $slug) {
                $tagQuery->where(function (Builder $inner) use ($value, $slug) {
                    $inner->where('slug', $slug)
                        ->orWhere('slug', $value)
                        ->orWhereRaw('LOWER(name) = ?', [mb_strtolower($value)]);
                });
            });
        }
    }

    /**
     * Parse filter value that may be array or JSON string.
     */
    private function parseArrayFilter($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $trim = trim($value);

            if ($trim === '') {
                return [];
            }

            if ($trim[0] === '[' || str_starts_with($trim, '%5B')) {
                $decoded = json_decode($trim, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }

                $urldec = urldecode($trim);
                $decoded = json_decode($urldec, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }

            return [$trim];
        }

        return [];
    }

    public function create(array $attributes): Course
    {
        return Course::create($attributes);
    }

    public function update(Course $course, array $attributes): Course
    {
        $course->fill($attributes)->save();

        return $course;
    }

    public function delete(Course $course): bool
    {
        return $course->delete();
    }
}
