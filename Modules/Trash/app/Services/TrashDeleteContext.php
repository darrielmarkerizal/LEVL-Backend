<?php

declare(strict_types=1);

namespace Modules\Trash\Services;

class TrashDeleteContext
{
    public int $activeDeleteOps = 0;

    public ?string $activeGroupUuid = null;

    
    public array $groupByModel = [];

    
    public array $rootByModel = [];

    
    public array $originalStatusByModel = [];

    
    public array $trashedStatusByModel = [];

    
    public array $processedDeleteModels = [];

    public function reset(): void
    {
        $this->activeDeleteOps = 0;
        $this->activeGroupUuid = null;
        $this->groupByModel = [];
        $this->rootByModel = [];
        $this->originalStatusByModel = [];
        $this->trashedStatusByModel = [];
        $this->processedDeleteModels = [];
    }
}
