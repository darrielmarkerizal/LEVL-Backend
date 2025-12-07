<?php

namespace Modules\Schemes\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Modules\Schemes\Contracts\Repositories\CourseRepositoryInterface;
use Modules\Schemes\Models\Course;

class CourseRepository extends BaseRepository implements CourseRepositoryInterface
{
    /**
     * Get the model class name.
     */
    protected function model(): string
    {
        return Course::class;
    }

    /**
     * Allowed filter keys mapped to database columns.
     *
     * @var array<int, string>
     */
    protected array $allowedFilters = ['status', 'level_tag', 'type', 'category_id'];

    /**
     * Allowed sort fields.
     *
     * @var array<int, string>
     */
    protected array $allowedSorts = ['id', 'code', 'title', 'created_at', 'updated_at', 'published_at'];

    /**
     * Default sort field.
     */
    protected string $defaultSort = 'title';

    /**
     * Default relations to load.
     *
     * @var array<int, string>
     */
    protected array $with = ['tags'];

    /**
     * Filter enum values.
     *
     * @var array<string, string>
     */
    private array $filterEnums = [
        'status' => 'draft|published|archived',
        'level_tag' => 'dasar|menengah|mahir',
        'type' => 'okupasi|kluster',
    ];

    public function findBySlug(string $slug): ?Course
    {
        return $this->query()->where('slug', $slug)->first();
    }

    public function paginate(array $params, int $perPage = 15): LengthAwarePaginator
    {
        $params = $this->normalizeParams($params);
        $query = $this->query();

        $this->applyTagFilters($query, $params['filter']['tag'] ?? null);

        return $this->filteredPaginate(
            $query,
            $params,
            $this->allowedFilters,
            $this->allowedSorts,
            'title',
            $perPage
        );
    }

    public function list(array $params): Collection
    {
        $params = $this->normalizeParams($params);
        $query = $this->query();

        $filter = $this->filter($query, $params)
            ->allowFilters($this->allowedFilters)
            ->allowSorts($this->allowedSorts)
            ->setDefaultSort('title');

        $filter->applyFiltersAndSorting($query);
        $this->applyTagFilters($query, $params['filter']['tag'] ?? null);

        return $query->get();
    }

    private function normalizeParams(array $params): array
    {
        $filters = $params['filter'] ?? [];
        if (! is_array($filters)) {
            $filters = [];
        }

        if (isset($filters['level'])) {
            $filters['level_tag'] = $filters['level'];
            unset($filters['level']);
        }

        if (isset($filters['category'])) {
            $filters['category_id'] = $this->parseCategoryFilter($filters['category']);
            unset($filters['category']);
        }

        if (isset($params['category_id'])) {
            $legacy = is_array($params['category_id']) ? $params['category_id'] : [$params['category_id']];
            $filters['category_id'] = array_merge($filters['category_id'] ?? [], $legacy);
            unset($params['category_id']);
        }

        if (isset($filters['category_id'])) {
            $filters['category_id'] = array_values(array_filter(
                array_map(fn ($value) => (int) $value, (array) $filters['category_id']),
                fn ($value) => $value > 0
            ));

            if (empty($filters['category_id'])) {
                unset($filters['category_id']);
            }
        }

        $params['filter'] = $filters;

        return $params;
    }

    private function parseCategoryFilter($value): array
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

    private function applyTagFilters(Builder $query, $filterTag): void
    {
        $tags = [];

        if (! empty($filterTag)) {
            $tags = $this->parseArrayFilter($filterTag);
        }

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
}
