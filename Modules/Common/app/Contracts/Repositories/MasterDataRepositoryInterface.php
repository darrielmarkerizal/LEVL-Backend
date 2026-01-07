<?php

namespace Modules\Common\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Modules\Common\Models\MasterDataItem;

interface MasterDataRepositoryInterface
{
    /**
     * Get paginated master data by type.
     */
    public function paginateByType(string $type, array $params = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all master data by type (no pagination).
     */
    public function allByType(string $type, array $params = []): Collection;

    /**
     * Get all distinct types.
     */
    public function getTypes(array $params = []): SupportCollection;

    /**
     * Find by ID within a type.
     */
    public function find(string $type, int $id): ?MasterDataItem;

    /**
     * Check if value exists in type.
     */
    public function valueExists(string $type, string $value, ?int $excludeId = null): bool;

    /**
     * Create a new master data item.
     * @return MasterDataItem
     */
    public function create(array $data);

    /**
     * Update a master data item.
     * @return MasterDataItem
     */
    public function update(MasterDataItem $item, array $data);

    /**
     * Delete a master data item.
     */
    public function delete(MasterDataItem $item): bool;
}
