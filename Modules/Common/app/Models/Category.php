<?php

declare(strict_types=1);

namespace Modules\Common\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Common\Traits\PgSearchable;
use Modules\Common\Enums\CategoryStatus;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Category extends Model
{
    use HasFactory, LogsActivity, PgSearchable, SoftDeletes;

    protected array $searchable_columns = [
        'name',
        'value',
        'description',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => 'Kategori baru telah dibuat',
                'updated' => 'Kategori telah diperbarui',
                'deleted' => 'Kategori telah dihapus',
                default => "Kategori {$eventName}",
            });
    }

    protected $fillable = [
        'name',
        'value',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => CategoryStatus::class,
    ];



    protected static function newFactory()
    {
        return \Database\Factories\CategoryFactory::new();
    }
}
