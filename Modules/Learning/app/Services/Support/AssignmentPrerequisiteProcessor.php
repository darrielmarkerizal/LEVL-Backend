<?php

declare(strict_types=1);

namespace Modules\Learning\Services\Support;

use Modules\Learning\Contracts\Repositories\AssignmentRepositoryInterface;
use Modules\Learning\DTOs\PrerequisiteCheckResult;
use Modules\Learning\Exceptions\AssignmentException;
use Modules\Schemes\Services\PrerequisiteService;

class AssignmentPrerequisiteProcessor
{
    public function __construct(
        private readonly AssignmentRepositoryInterface $repository,
        private readonly PrerequisiteService $prerequisiteService
    ) {}

    public function checkPrerequisites(
        int $assignmentId,
        int $studentId
    ): PrerequisiteCheckResult {
        $assignment = $this->repository->find($assignmentId);

        if (! $assignment) {
            throw AssignmentException::notFound();
        }

        $accessCheck = $this->prerequisiteService->checkAssignmentAccess($assignment, $studentId);

        if ($accessCheck['accessible']) {
            return PrerequisiteCheckResult::pass();
        }

        $missing = collect($accessCheck['missing'] ?? []);

        if ($missing->isEmpty()) {
            return PrerequisiteCheckResult::pass();
        }

        return PrerequisiteCheckResult::fail($missing);
    }
}
