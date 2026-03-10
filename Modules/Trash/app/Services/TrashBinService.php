<?php

declare(strict_types=1);

namespace Modules\Trash\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Trash\Models\TrashBin;
use Spatie\MediaLibrary\HasMedia;

class TrashBinService
{
    private const RETENTION_DAYS = 30;

    /** @var array<string, string> */
    private const RESOURCE_TYPES = [
        \Modules\Schemes\Models\Course::class => 'course',
        \Modules\Schemes\Models\Unit::class => 'unit',
        \Modules\Schemes\Models\Lesson::class => 'lesson',
        \Modules\Learning\Models\Quiz::class => 'quiz',
        \Modules\Learning\Models\Assignment::class => 'assignment',
        \Modules\Auth\Models\User::class => 'user',
        \Modules\Gamification\Models\Badge::class => 'badge',
        \Modules\Content\Models\News::class => 'news',
    ];

    /** @var array<string, string> */
    private const TRASHED_STATUS_MAP = [
        \Modules\Auth\Models\User::class => 'inactive',
        \Modules\Schemes\Models\Course::class => 'archived',
        \Modules\Schemes\Models\Unit::class => 'draft',
        \Modules\Schemes\Models\Lesson::class => 'draft',
        \Modules\Learning\Models\Quiz::class => 'archived',
        \Modules\Learning\Models\Assignment::class => 'archived',
        \Modules\Content\Models\News::class => 'archived',
    ];

    private static int $activeDeleteOps = 0;

    private static ?string $activeGroupUuid = null;

    /** @var array<string, string> */
    private static array $groupByModel = [];

    /** @var array<string, array{type: string, id: int}> */
    private static array $rootByModel = [];

    /** @var array<string, string|null> */
    private static array $originalStatusByModel = [];

    /** @var array<string, string|null> */
    private static array $trashedStatusByModel = [];

    /** @var array<string, bool> */
    private static array $processedDeleteModels = [];

    public function beforeSoftDelete(Model $model): void
    {
        if (! $this->isSupported($model)) {
            return;
        }

        $key = $this->modelKey($model);
        if (isset(self::$processedDeleteModels[$key])) {
            return;
        }

        if (self::$activeDeleteOps === 0) {
            self::$activeGroupUuid = (string) Str::uuid();
            self::$rootByModel[$key] = [
                'type' => get_class($model),
                'id' => (int) $model->getKey(),
            ];
        }

        self::$activeDeleteOps++;
        self::$processedDeleteModels[$key] = true;
        self::$groupByModel[$key] = (string) self::$activeGroupUuid;

        if (! isset(self::$rootByModel[$key])) {
            $firstRoot = reset(self::$rootByModel);
            self::$rootByModel[$key] = $firstRoot ?: [
                'type' => get_class($model),
                'id' => (int) $model->getKey(),
            ];
        }

        self::$originalStatusByModel[$key] = $this->readStatusValue($model);
        self::$trashedStatusByModel[$key] = $this->resolveTrashedStatus($model);

        $this->applyTrashedStatus($model);
        $this->cascadeDeleteChildren($model);
    }

    public function recordSoftDeleted(Model $model): void
    {
        if (! $this->isSupported($model)) {
            return;
        }

        $key = $this->modelKey($model);
        $resourceType = self::RESOURCE_TYPES[get_class($model)] ?? 'unknown';
        $root = self::$rootByModel[$key] ?? [
            'type' => get_class($model),
            'id' => (int) $model->getKey(),
        ];

        TrashBin::query()->updateOrCreate(
            [
                'trashable_type' => get_class($model),
                'trashable_id' => (int) $model->getKey(),
            ],
            [
                'resource_type' => $resourceType,
                'group_uuid' => self::$groupByModel[$key] ?? (string) Str::uuid(),
                'root_resource_type' => $root['type'],
                'root_resource_id' => $root['id'],
                'original_status' => self::$originalStatusByModel[$key] ?? null,
                'trashed_status' => self::$trashedStatusByModel[$key] ?? null,
                'deleted_by' => $this->resolveActorId(),
                'deleted_at' => now(),
                'expires_at' => now()->addDays(self::RETENTION_DAYS),
                'metadata' => [
                    'title' => $this->extractDisplayTitle($model),
                    'course_id' => $this->extractCourseId($model),
                ],
                'restored_at' => null,
                'force_deleted_at' => null,
            ]
        );

        $this->releaseDeleteContext($key);
    }

    public function afterRestored(Model $model): void
    {
        if (! $this->isSupported($model)) {
            return;
        }

        $bin = TrashBin::query()
            ->where('trashable_type', get_class($model))
            ->where('trashable_id', (int) $model->getKey())
            ->first();

        if ($bin && $this->hasStatusColumn($model) && ! empty($bin->original_status)) {
            $model->forceFill(['status' => $bin->original_status]);
            $model->saveQuietly();
        }

        if ($bin) {
            $bin->delete();
        }
    }

    public function afterForceDeleted(Model $model): void
    {
        TrashBin::query()
            ->where('trashable_type', get_class($model))
            ->where('trashable_id', (int) $model->getKey())
            ->delete();
    }

    public function restoreFromTrashBin(TrashBin $bin): bool
    {
        return DB::transaction(fn () => $this->restoreSingle($bin));
    }

    public function forceDeleteFromTrashBin(TrashBin $bin): bool
    {
        return DB::transaction(function () use ($bin): bool {
            if ($this->isRootBin($bin)) {
                $bins = TrashBin::query()
                    ->where('group_uuid', $bin->group_uuid)
                    ->orderBy('id')
                    ->get();

                foreach ($bins as $item) {
                    $this->forceDeleteSingle($item);
                }

                return true;
            }

            return $this->forceDeleteSingle($bin);
        });
    }

    public function purgeExpired(): int
    {
        $count = 0;

        TrashBin::query()
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($bins) use (&$count): void {
                foreach ($bins as $bin) {
                    if ($this->forceDeleteFromTrashBin($bin)) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    public function forceDeleteAll(?string $resourceType = null): int
    {
        $count = 0;

        $query = TrashBin::query()->orderBy('id');
        if ($resourceType !== null) {
            $query->where('resource_type', $resourceType);
        }

        $query->chunkById(100, function ($bins) use (&$count): void {
            foreach ($bins as $bin) {
                if ($this->forceDeleteFromTrashBin($bin)) {
                    $count++;
                }
            }
        });

        return $count;
    }

    public function forceDeleteMany(array $ids): int
    {
        $count = 0;

        TrashBin::query()
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->chunkById(100, function ($bins) use (&$count): void {
                foreach ($bins as $bin) {
                    if ($this->forceDeleteFromTrashBin($bin)) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    public function restoreAll(?string $resourceType = null): int
    {
        $count = 0;

        $query = TrashBin::query()->orderBy('id');
        if ($resourceType !== null) {
            $query->where('resource_type', $resourceType);
        }

        $query->chunkById(100, function ($bins) use (&$count): void {
            foreach ($bins as $bin) {
                if ($this->restoreFromTrashBin($bin)) {
                    $count++;
                }
            }
        });

        return $count;
    }

    public function restoreMany(array $ids): int
    {
        $count = 0;

        TrashBin::query()
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->chunkById(100, function ($bins) use (&$count): void {
                foreach ($bins as $bin) {
                    if ($this->restoreFromTrashBin($bin)) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    /**
     * @return array<int, string>
     */
    public function getSupportedResourceTypes(): array
    {
        $types = array_values(array_unique(array_values(self::RESOURCE_TYPES)));
        sort($types);

        return $types;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function getSupportedResourceTypeOptions(): array
    {
        return $this->toSourceTypeOptions($this->getSupportedResourceTypes());
    }

    public function shouldRunAsyncCascade(TrashBin $bin, int $groupCount): bool
    {
        return $this->isRootBin($bin) && $groupCount > 1;
    }

    /**
     * @param array<int, string> $types
     * @return array<int, array{value: string, label: string}>
     */
    public function toSourceTypeOptions(array $types): array
    {
        $normalized = array_values(array_unique(array_filter($types, fn ($value): bool => is_string($value) && $value !== '')));
        sort($normalized);

        return array_map(
            fn (string $value): array => [
                'value' => $value,
                'label' => $this->resolveSourceTypeLabel($value),
            ],
            $normalized
        );
    }

    private function resolveSourceTypeLabel(string $type): string
    {
        $key = "messages.trash_bins.source_type_labels.{$type}";
        $translated = __($key);

        if ($translated !== $key) {
            return $translated;
        }

        return (string) Str::of($type)->replace('_', ' ')->title();
    }

    private function restoreSingle(TrashBin $bin): bool
    {
        $class = $bin->trashable_type;
        if (! class_exists($class)) {
            $bin->delete();

            return false;
        }

        $query = $this->queryWithTrashed($class);
        $model = $query?->find($bin->trashable_id);

        if (! $model) {
            $bin->delete();

            return false;
        }

        if (method_exists($model, 'trashed') && $model->trashed()) {
            $model->restore();
        }

        $bin->delete();

        return true;
    }

    private function forceDeleteSingle(TrashBin $bin): bool
    {
        $class = $bin->trashable_type;
        if (! class_exists($class)) {
            $bin->delete();

            return false;
        }

        $query = $this->queryWithTrashed($class);
        $model = $query?->find($bin->trashable_id);

        if ($model) {
            $this->deleteModelMedia($model);

            if ($model instanceof \Modules\Gamification\Models\Badge) {
                $model->rules()->delete();
            }

            if (method_exists($model, 'forceDelete')) {
                $model->forceDelete();
            }
        }

        $bin->delete();

        return true;
    }

    private function deleteModelMedia(Model $model): void
    {
        if (! $model instanceof HasMedia) {
            return;
        }

        $model->media()->get()->each(function ($media): void {
            $media->delete();
        });
    }

    private function cascadeDeleteChildren(Model $model): void
    {
        if ($model instanceof \Modules\Schemes\Models\Course) {
            $model->units()->get()->each(function ($unit): void {
                if (! $unit->trashed()) {
                    $unit->delete();
                }
            });
        }

        if ($model instanceof \Modules\Schemes\Models\Unit) {
            $model->lessons()->get()->each(function ($lesson): void {
                if (! $lesson->trashed()) {
                    $lesson->delete();
                }
            });

            $model->quizzes()->get()->each(function ($quiz): void {
                if (! $quiz->trashed()) {
                    $quiz->delete();
                }
            });

            $model->assignments()->get()->each(function ($assignment): void {
                if (! $assignment->trashed()) {
                    $assignment->delete();
                }
            });
        }
    }

    private function applyTrashedStatus(Model $model): void
    {
        if (! $this->hasStatusColumn($model)) {
            return;
        }

        $targetStatus = $this->resolveTrashedStatus($model);
        if (! $targetStatus) {
            return;
        }

        $current = $this->readStatusValue($model);
        if ($current === $targetStatus) {
            return;
        }

        $model->forceFill(['status' => $targetStatus]);
        $model->saveQuietly();
    }

    private function hasStatusColumn(Model $model): bool
    {
        static $cache = [];

        $table = $model->getTable();
        if (! array_key_exists($table, $cache)) {
            $cache[$table] = Schema::hasColumn($table, 'status');
        }

        return $cache[$table];
    }

    private function readStatusValue(Model $model): ?string
    {
        if (! $this->hasStatusColumn($model)) {
            return null;
        }

        $status = $model->getAttribute('status');

        if (is_object($status) && isset($status->value)) {
            return (string) $status->value;
        }

        return $status !== null ? (string) $status : null;
    }

    private function resolveTrashedStatus(Model $model): ?string
    {
        return self::TRASHED_STATUS_MAP[get_class($model)] ?? null;
    }

    private function resolveActorId(): ?int
    {
        $id = auth()->id();

        return $id ? (int) $id : null;
    }

    private function extractDisplayTitle(Model $model): ?string
    {
        foreach (['title', 'name', 'code', 'slug', 'email'] as $field) {
            $value = $model->getAttribute($field);
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function extractCourseId(Model $model): ?int
    {
        if ($model instanceof \Modules\Schemes\Models\Course) {
            return (int) $model->id;
        }

        if ($model instanceof \Modules\Schemes\Models\Unit) {
            return (int) $model->course_id;
        }

        if ($model instanceof \Modules\Schemes\Models\Lesson) {
            return $model->unit ? (int) $model->unit->course_id : null;
        }

        if (method_exists($model, 'getCourseId')) {
            $courseId = $model->getCourseId();

            return $courseId !== null ? (int) $courseId : null;
        }

        return null;
    }

    private function isRootBin(TrashBin $bin): bool
    {
        return $bin->root_resource_type === $bin->trashable_type
            && (int) $bin->root_resource_id === (int) $bin->trashable_id;
    }

    private function queryWithTrashed(string $class): ?\Illuminate\Database\Eloquent\Builder
    {
        if (! class_exists($class) || ! is_subclass_of($class, Model::class)) {
            return null;
        }

        $uses = class_uses_recursive($class);
        if (! in_array(SoftDeletes::class, $uses, true)) {
            return $class::query();
        }

        return $class::withTrashed();
    }

    private function isSupported(Model $model): bool
    {
        return array_key_exists(get_class($model), self::RESOURCE_TYPES);
    }

    private function modelKey(Model $model): string
    {
        return get_class($model).':'.$model->getKey();
    }

    private function releaseDeleteContext(string $key): void
    {
        self::$activeDeleteOps = max(0, self::$activeDeleteOps - 1);

        unset(
            self::$processedDeleteModels[$key],
            self::$groupByModel[$key],
            self::$rootByModel[$key],
            self::$originalStatusByModel[$key],
            self::$trashedStatusByModel[$key]
        );

        if (self::$activeDeleteOps === 0) {
            self::$activeGroupUuid = null;
            self::$groupByModel = [];
            self::$rootByModel = [];
            self::$originalStatusByModel = [];
            self::$trashedStatusByModel = [];
            self::$processedDeleteModels = [];
        }
    }
}
