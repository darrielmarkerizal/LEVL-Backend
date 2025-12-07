<?php

namespace App\Services;

use App\Models\MasterDataItem;
use App\Repositories\MasterDataRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class MasterDataService
{
    public function __construct(private readonly MasterDataRepository $repository) {}

    /**
     * Get all master data types.
     */
    public function getTypes(): Collection
    {
        return $this->repository->getTypes();
    }

    /**
     * List master data by type with pagination.
     */
    public function paginate(string $type, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($type, $perPage);
    }

    /**
     * Get all master data by type (no pagination).
     */
    public function all(string $type): Collection
    {
        return $this->repository->all($type);
    }

    /**
     * Find a master data item by type and id.
     */
    public function find(string $type, int $id): ?MasterDataItem
    {
        return $this->repository->find($type, $id);
    }

    /**
     * Create a new master data item.
     */
    public function create(string $type, array $data): MasterDataItem
    {
        return $this->repository->create([
            'type' => $type,
            'value' => $data['value'],
            'label' => $data['label'],
            'metadata' => $data['metadata'] ?? null,
            'is_system' => false,
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    /**
     * Update a master data item.
     */
    public function update(string $type, int $id, array $data): ?MasterDataItem
    {
        $item = $this->repository->find($type, $id);
        if (! $item) {
            return null;
        }

        // System items: don't allow changing value
        if ($item->is_system && isset($data['value'])) {
            unset($data['value']);
        }

        return $this->repository->update($item, $data);
    }

    /**
     * Delete a master data item.
     */
    public function delete(string $type, int $id): bool|string
    {
        $item = $this->repository->find($type, $id);
        if (! $item) {
            return 'not_found';
        }

        if ($item->is_system) {
            return 'system_protected';
        }

        return $this->repository->delete($item);
    }

    /**
     * Check if value already exists.
     */
    public function valueExists(string $type, string $value, ?int $excludeId = null): bool
    {
        return $this->repository->valueExists($type, $value, $excludeId);
    }
}
