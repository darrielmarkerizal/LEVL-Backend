<?php

declare(strict_types=1);

namespace Modules\Schemes\Contracts\Repositories;

use Modules\Schemes\Models\Tag;

interface TagRepositoryInterface
{
    
    public function findById(int $id): ?Tag;

    
    public function create(array $data): Tag;
}
