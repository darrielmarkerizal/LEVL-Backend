<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Repositories\ActivityLogRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Support\Traits\ProvidesMetadata;

class ActivityLogService
{
  use ProvidesMetadata;

  public function __construct(private ActivityLogRepository $repository) {}

  public function paginate(array $params): array
  {
    $perPage = max(1, min((int) ($params['per_page'] ?? 15), 100));
    $paginator = $this->repository->paginate($params, $perPage);

    $filterOptions = $this->getFilterOptions();

    $metadata = $this->buildMetadata(
      allowedSorts: ['id', 'created_at', 'log_name', 'event'],
      filters: [
        'log_name' => [
          'type' => 'select',
          'options' => $filterOptions['log_names']
            ->map(fn($name) => ['value' => $name, 'label' => $name])
            ->values()
            ->all(),
        ],
        'browser' => [
          'type' => 'select',
          'options' => $filterOptions['browsers']
            ->map(fn($browser) => ['value' => $browser, 'label' => $browser])
            ->values()
            ->all(),
        ],
        'device_type' => [
          'type' => 'select',
          'options' => $this->buildSelectOptions([
            'desktop' => __('activity_logs.device_types.desktop'),
            'mobile' => __('activity_logs.device_types.mobile'),
            'tablet' => __('activity_logs.device_types.tablet'),
          ]),
        ],
        'platform' => [
          'type' => 'select',
          'options' => $filterOptions['platforms']
            ->map(fn($val) => ['value' => $val, 'label' => $val])
            ->values()
            ->all(),
        ],
        'subject_type' => [
          'type' => 'select',
          'options' => $filterOptions['subject_types']
            ->map(fn($val) => ['value' => $val, 'label' => $val])
            ->values()
            ->all(),
        ],
        'causer_type' => [
          'type' => 'select',
          'options' => $filterOptions['causer_types']
            ->map(fn($val) => ['value' => $val, 'label' => $val])
            ->values()
            ->all(),
        ],
        'subject_id' => ['type' => 'number'],
        'causer_id' => ['type' => 'number'],
        'created_at' => [
          'type' => 'date_range',
        ],
      ],
      translationPrefix: 'activity_logs',
    );

    return [
      'paginator' => $paginator,
      'metadata' => $metadata,
    ];
  }

  /**
   * Get single activity log by ID.
   */
  public function find(int $id): ?ActivityLog
  {
    return $this->repository->find($id);
  }

  /**
   * Get distinct filter options for activity logs.
   */
  public function getFilterOptions(): array
  {
    return [
      'log_names' => ActivityLog::query()
        ->distinct()
        ->whereNotNull('log_name')
        ->pluck('log_name')
        ->filter()
        ->sort()
        ->values(),
      'browsers' => ActivityLog::query()
        ->distinct()
        ->whereNotNull('browser')
        ->pluck('browser')
        ->filter()
        ->sort()
        ->values(),
      'platforms' => ActivityLog::query()
        ->distinct()
        ->whereNotNull('platform')
        ->pluck('platform')
        ->filter()
        ->sort()
        ->values(),
      'subject_types' => ActivityLog::query()
        ->distinct()
        ->whereNotNull('subject_type')
        ->pluck('subject_type')
        ->filter()
        ->sort()
        ->values(),
      'causer_types' => ActivityLog::query()
        ->distinct()
        ->whereNotNull('causer_type')
        ->pluck('causer_type')
        ->filter()
        ->sort()
        ->values(),
    ];
  }
}
