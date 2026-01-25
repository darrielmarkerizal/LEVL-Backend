<?php

declare(strict_types=1);

namespace Modules\Grading\Contracts\Services;

use Illuminate\Support\Collection;
use Modules\Grading\Models\Appeal;

interface AppealServiceInterface
{
    public function submitAppeal(int $submissionId, int $studentId, string $reason, array $files = []): Appeal;

    public function getAppealForUser(Appeal $appeal, int $userId): Appeal;

    public function approveAppeal(int $appealId, int $instructorId): void;

    public function denyAppeal(int $appealId, int $instructorId, string $reason): void;

    public function getPendingAppeals(int $instructorId): Collection;

    public function getAppeals(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator;
}
