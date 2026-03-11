<?php

declare(strict_types=1);

namespace Modules\Trash\Services;

class TrashDeleteContext
{
    public int $activeDeleteOps = 0;

    public ?string $activeGroupUuid = null;

    /** @var array<string, string> */
    public array $groupByModel = [];

    /** @var array<string, array{type: string, id: int}> */
    public array $rootByModel = [];

    /** @var array<string, string|null> */
    public array $originalStatusByModel = [];

    /** @var array<string, string|null> */
    public array $trashedStatusByModel = [];

    /** @var array<string, bool> */
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
