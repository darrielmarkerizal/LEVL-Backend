<?php

declare(strict_types=1);

namespace Modules\Common\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Common\Contracts\Repositories\AuditRepositoryInterface;
use Modules\Common\Models\AuditLog;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AuditRepository implements AuditRepositoryInterface
{
    public function create(array $data): AuditLog
    {
        return AuditLog::create($data);
    }

    public function search(array $filters): Collection
    {
        return cache()->tags(['common', 'audit_logs'])->remember(
            'common:audit_logs:search:'.md5(json_encode($filters)),
            300,
            function () use ($filters) {
                $request = new Request(['filter' => $filters]);

                return QueryBuilder::for(AuditLog::class, $request)
                    ->with(['causer', 'subject']) 
                    ->allowedFilters([
                        AllowedFilter::callback('action', fn ($q, $v) => $q->where('description', $v)),
                        AllowedFilter::callback('actor_id', fn ($q, $v) => $q->where('causer_id', $v)),
                        AllowedFilter::callback('actor_type', fn ($q, $v) => $q->where('causer_type', $v)),
                        AllowedFilter::exact('subject_id'),
                        AllowedFilter::exact('subject_type'),
                        AllowedFilter::callback('start_date', fn ($q, $v) => $q->where('created_at', '>=', $v)),
                        AllowedFilter::callback('end_date', fn ($q, $v) => $q->where('created_at', '<=', $v)),
                        AllowedFilter::callback('search', fn ($q, $v) => $q->where('description', 'ILIKE', "%{$v}%")),
                    ])
                    ->allowedSorts(['created_at', 'description', 'causer_id'])
                    ->defaultSort('-created_at')
                    ->get();
            }
        );
    }

    
    public function findBySubject(string $subjectType, int $subjectId): Collection
    {
        return AuditLog::query()
            ->with(['causer', 'subject']) 
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    
    public function findByActor(string $actorType, int $actorId): Collection
    {
        return AuditLog::query()
            ->with(['causer', 'subject']) 
            ->where('causer_type', $actorType) 
            ->where('causer_id', $actorId) 
            ->orderBy('created_at', 'desc')
            ->get();
    }

    
    public function findByAction(string $action): Collection
    {
        return AuditLog::query()
            ->with(['causer', 'subject']) 
            ->where('description', $action) 
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
