<?php

declare(strict_types=1);

namespace Modules\Common\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Auth\Models\User;
use Modules\Common\Models\AuditLog;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AuditLogQueryService
{
    public function canAccess(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $user->hasRole('Admin') || $user->hasRole('Superadmin');
    }

    public function searchAndPaginate(array $validated): LengthAwarePaginator
    {
        $perPage = (int) ($validated['per_page'] ?? 15);
        $perPage = max(1, min($perPage, 100));

        $query = AuditLog::query();
        $search = $validated['search'] ?? null;

        // Use PgSearchable trait for search
        if ($search && trim($search) !== '') {
            $query->search($search);
        }

        // Also search in properties JSON if search term provided
        if ($search && trim($search) !== '') {
            $query->orWhereRaw('properties::text ILIKE ?', ["%{$search}%"]);
        }

        return cache()->tags(['common', 'audit_logs'])->remember(
            "common:audit_logs:paginate:{$perPage}:".md5(json_encode($validated)),
            300,
            function () use ($query, $perPage) {
                return QueryBuilder::for($query)
                    ->with('causer') // Changed from 'actor' to 'causer' (Spatie's relationship name)
                    ->allowedFilters([
                        AllowedFilter::callback('action', fn ($q, $v) => $q->where('description', $v)),
                        AllowedFilter::scope('actions', 'action_in'),
                        AllowedFilter::callback('actor_id', fn ($q, $v) => $q->where('causer_id', $v)),
                        AllowedFilter::callback('actor_type', fn ($q, $v) => $q->where('causer_type', $v)),
                        AllowedFilter::exact('subject_id'),
                        AllowedFilter::exact('subject_type'),
                        AllowedFilter::scope('created_between'),
                        AllowedFilter::scope('context_contains'),
                        AllowedFilter::scope('assignment_id'),
                        AllowedFilter::scope('student_id'),
                    ])
                    ->allowedSorts(['created_at', 'id', 'description', 'causer_id'])
                    ->defaultSort('-created_at')
                    ->paginate($perPage)
                    ->appends(request()->query());
            }
        );
    }

    public function findById(int $id): ?AuditLog
    {
        return AuditLog::with('causer')->find($id); // Changed from 'actor' to 'causer'
    }

    public function getAvailableActions(): array
    {
        return [
            AssessmentAuditService::ACTION_SUBMISSION_CREATED,
            AssessmentAuditService::ACTION_STATE_TRANSITION,
            AssessmentAuditService::ACTION_GRADING,
            AssessmentAuditService::ACTION_ANSWER_KEY_CHANGE,
            AssessmentAuditService::ACTION_GRADE_OVERRIDE,
            AssessmentAuditService::ACTION_OVERRIDE_GRANT,
        ];
    }
}
