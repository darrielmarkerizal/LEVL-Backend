<?php

declare(strict_types=1);

namespace Modules\Common\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Common\Models\MasterDataItem;
use Modules\Common\Repositories\MasterDataRepository;
use Modules\Common\Support\MasterDataEnumMapper;
use Modules\Schemes\Models\Course;

class MasterDataService
{
    private const CACHE_TAG = 'master_data';

    private const CACHE_TTL = 3600;

    public function __construct(
        private readonly MasterDataRepository $repository,
        private readonly MasterDataEnumMapper $enumMapper,
        private readonly \Modules\Common\Support\MasterDataProcessor $processor
    ) {}

    public function get(string $type): array|Collection
    {
        $staticTypes = $this->enumMapper->getStaticTypes();

        if (isset($staticTypes[$type])) {
            return $staticTypes[$type]();
        }

        return Cache::tags([self::CACHE_TAG])->remember(
            "type:{$type}",
            self::CACHE_TTL,
            fn () => $this->repository->allByType($type, ['filter' => ['is_active' => true]])
        );
    }

    public function find(string $type, int $id): ?MasterDataItem
    {
        return $this->repository->find($type, $id);
    }

    public function paginate(string $type, int $perPage = 15): LengthAwarePaginator
    {
        if ($this->enumMapper->isStaticType($type)) {
            $data = $this->get($type);
            $collection = collect($data);
            $page = request()->input('page', 1);
            $total = $collection->count();
            $items = $collection->forPage($page, $perPage)->values();

            return new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }

        return $this->repository->paginateByType($type, [], $perPage);
    }

    public function getAll(string $type, array $params = []): Collection|array
    {
        if ($this->enumMapper->isStaticType($type)) {
            return $this->get($type);
        }

        return $this->repository->allByType($type, $params);
    }

    public function isCrudAllowed(string $type): bool
    {
        return ! $this->enumMapper->isStaticType($type);
    }

    public function getAvailableTypes(array $params = []): LengthAwarePaginator
    {
        $staticTypes = $this->buildStaticTypesList();
        $dbTypes = $this->buildDatabaseTypesList();
        $merged = $staticTypes->concat($dbTypes);

        return $this->processor->process($merged, $params);
    }

    public function create(string $type, array $data): MasterDataItem
    {
        $data['type'] = $type;

        $item = $this->repository->create($data);

        Cache::tags([self::CACHE_TAG])->flush();

        return $item;
    }

    public function update(string $type, int $id, array $data): MasterDataItem
    {
        $item = $this->repository->find($type, $id);

        if (! $item) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Master data item not found.');
        }

        $updated = $this->repository->update($item, $data);

        Cache::tags([self::CACHE_TAG])->flush();

        return $updated;
    }

    public function getStudents(?string $search = null): Collection
    {
        $query = \Modules\Auth\Models\User::select(['id', 'name', 'username', 'email'])
            ->role('Student')
            ->orderBy('name');

        if (! empty($search)) {
            $query->search($search);
        }

        return $query->get();
    }

    public function getCourses(?string $search = null): Collection
    {
        $user = auth('api')->user();
        $query = Course::select(['id', 'title', 'slug', 'instructor_id', 'status']);

        if ($user && ! $user->hasRole('Superadmin')) {
            if ($user->hasRole(['Admin', 'Instructor'])) {
                $query->where(function ($q) use ($user) {
                    $q->where('instructor_id', $user->id)
                        ->orWhereHas('admins', fn ($subQuery) => $subQuery->where('user_id', $user->id));
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if (! empty($search)) {
            $query->search($search);
        }

        return $query->orderBy('title')->get();
    }

    public function delete(string $type, int $id): bool
    {
        $item = $this->repository->find($type, $id);

        if (! $item) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Master data item not found.');
        }

        $deleted = $this->repository->delete($item);

        if ($deleted) {
            Cache::tags([self::CACHE_TAG])->flush();
        }

        return $deleted;
    }

    private function buildStaticTypesList(): Collection
    {
        $staticTypes = $this->enumMapper->getStaticTypes();

        return collect(array_keys($staticTypes))->map(function ($key) use ($staticTypes) {
            $data = $staticTypes[$key]();
            $count = is_array($data) ? count($data) : $data->count();

            return [
                'type' => $key,
                'label' => __("messages.master_data.{$key}") ?? ucwords(str_replace('-', ' ', $key)),
                'is_crud' => false,
                'count' => $count,
                'last_updated' => null,
            ];
        });
    }

    private function buildDatabaseTypesList(): Collection
    {
        return $this->repository->getTypes()->map(function ($item) {
            
            return $this->transformTypeItem($item);
        });
    }

    private function transformTypeItem($item): array
    {
        $labelMap = ['categories' => 'Kategori', 'tags' => 'Tags'];

        
        $type = is_object($item) ? $item->type : $item['type'];
        $count = is_object($item) ? $item->count : $item['count'];
        $last_updated = is_object($item) ? $item->last_updated : $item['last_updated'];

        return [
            'key' => $type,
            'type' => $type,
            'label' => $labelMap[$type] ?? ucwords(str_replace('-', ' ', $type)),
            'count' => $count,
            'last_updated' => $last_updated,
            'is_crud' => true,
        ];
    }

    public function extractQueryParams(array $query): array
    {
        return [
            'search' => $query['search'] ?? null,
            'sort' => $query['sort'] ?? null,
            'sort_order' => $query['sort_order'] ?? null,
            'page' => $query['page'] ?? null,
            'per_page' => $query['per_page'] ?? null,
            'filter' => $query['filter'] ?? null,
        ];
    }

    public function getValidationRules(bool $isUpdate = false): array
    {
        return [
            'value' => ($isUpdate ? 'sometimes|' : '').'required|string|max:255',
            'label' => ($isUpdate ? 'sometimes|' : '').'required|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'metadata' => 'nullable|array',
        ];
    }
}
