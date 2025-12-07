<?php

namespace App\Repositories;

use App\Models\MasterDataItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class MasterDataRepository
{
    /**
     * Get paginated master data by type using Spatie Query Builder.
     *
     * Supports:
     * - filter[is_active], filter[is_system], filter[value], filter[label]
     * - filter[search] for global search
     * - sort: value, label, sort_order, created_at, updated_at (prefix with - for desc)
     */
    public function paginate(string $type, int $perPage = 15): LengthAwarePaginator
    {
        return $this->buildQuery($type)->paginate($perPage);
    }

    /**
     * Get all master data by type (no pagination).
     */
    public function all(string $type): Collection
    {
        return $this->buildQuery($type)->get();
    }

    /**
     * Get all distinct types.
     */
    public function getTypes(): Collection
    {
        return MasterDataItem::select('type')
            ->distinct()
            ->orderBy('type')
            ->get();
    }

    /**
     * Find by ID within a type.
     */
    public function find(string $type, int $id): ?MasterDataItem
    {
        return MasterDataItem::where('type', $type)
            ->where('id', $id)
            ->first();
    }

    /**
     * Check if value exists in type.
     */
    public function valueExists(string $type, string $value, ?int $excludeId = null): bool
    {
        return MasterDataItem::where('type', $type)
            ->where('value', $value)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }

    /**
     * Create a new master data item.
     */
    public function create(array $attributes): MasterDataItem
    {
        return MasterDataItem::create($attributes);
    }

    /**
     * Update a master data item.
     */
    public function update(MasterDataItem $item, array $attributes): MasterDataItem
    {
        $item->fill($attributes)->save();

        return $item;
    }

    /**
     * Delete a master data item.
     */
    public function delete(MasterDataItem $item): bool
    {
        return $item->delete();
    }

    /**
     * Build query with Spatie Query Builder + Scout search.
     */
    private function buildQuery(string $type): QueryBuilder
    {
        $searchQuery = request('filter.search');

        $builder = QueryBuilder::for(MasterDataItem::class)
            ->where('type', $type);

        // If search query exists, use Scout to get matching IDs
        if ($searchQuery && trim($searchQuery) !== '') {
            $ids = MasterDataItem::search($searchQuery)
                ->query(fn ($q) => $q->where('type', $type))
                ->keys()
                ->toArray();
            $builder->whereIn('id', $ids);
        }

        return $builder
            ->allowedFilters([
                AllowedFilter::exact('is_active'),
                AllowedFilter::exact('is_system'),
                AllowedFilter::partial('value'),
                AllowedFilter::partial('label'),
            ])
            ->allowedSorts(['value', 'label', 'sort_order', 'created_at', 'updated_at'])
            ->defaultSort('sort_order');
    }
}
