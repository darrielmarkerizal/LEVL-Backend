<?php

namespace App\Repositories;

use App\Models\ActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\AllowedFilter;

class ActivityLogRepository extends BaseRepository
{
    
    protected array $allowedFilters = [
        'log_name',
        'event',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'device_type',
        'browser',
        'platform',
        'ip_address',
    ];

    
    protected array $allowedSorts = ['id', 'created_at', 'event', 'log_name'];

    
    protected string $defaultSort = '-created_at';

    
    protected array $with = ['causer', 'subject'];

    protected function model(): string
    {
        return ActivityLog::class;
    }

    
    public function paginate(array $params = [], int $perPage = 15): LengthAwarePaginator
    {
        $request = new Request($params);
        $search = trim((string) ($params['search'] ?? ''));

        $query = $this->query()->with(['causer' => function ($morphTo) {
            $morphTo->morphWith([
                \Modules\Auth\Models\User::class => ['latestActivity'],
            ]);
        }]);

        if ($search !== '') {
            $query->search($search);
        }

        $allowedFilters = array_merge(
            $this->allowedFilters,
            [
                AllowedFilter::callback('created_at', function ($builder, $value) {
                    $from = Arr::get($value, 'from');
                    $to = Arr::get($value, 'to');

                    if ($from) {
                        $builder->whereDate('created_at', '>=', Carbon::parse($from)->startOfDay());
                    }

                    if ($to) {
                        $builder->whereDate('created_at', '<=', Carbon::parse($to)->endOfDay());
                    }
                }),
            ],
        );

        return \Spatie\QueryBuilder\QueryBuilder::for($query, $request)
            ->allowedFilters($allowedFilters)
            ->allowedSorts($this->allowedSorts)
            ->defaultSort($this->defaultSort)
            ->paginate($perPage);
    }

    
    public function find(int $id): ?ActivityLog
    {
        return $this->query()->find($id);
    }
}
