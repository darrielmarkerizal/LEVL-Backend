<?php

namespace Modules\Common\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Modules\Common\Models\MasterDataItem;

interface MasterDataRepositoryInterface
{
    
    public function paginateByType(string $type, array $params = [], int $perPage = 15): LengthAwarePaginator;

    
    public function allByType(string $type, array $params = []): Collection;

    
    public function getTypes(array $params = []): SupportCollection;

    
    public function find(string $type, int $id): ?MasterDataItem;

    
    public function valueExists(string $type, string $value, ?int $excludeId = null): bool;

    
    public function create(array $data);

    
    public function update(MasterDataItem $item, array $data);

    
    public function delete(MasterDataItem $item): bool;
}
