<?php

declare(strict_types=1);

namespace Modules\Common\Contracts\Repositories;

use Illuminate\Support\Collection;
use Modules\Common\Models\AuditLog;

interface AuditRepositoryInterface
{
    public function create(array $data): AuditLog;

    public function search(array $filters): Collection;

    public function findBySubject(string $subjectType, int $subjectId): Collection;

    public function findByActor(string $actorType, int $actorId): Collection;

    public function findByAction(string $action): Collection;
}
