<?php

declare(strict_types=1);

namespace Modules\Schemes\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Common\Traits\PgSearchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Unit extends Model
{
    use HasFactory, HasSlug, LogsActivity, PgSearchable;

    protected array $searchable_columns = [
        'title',
        'description',
        'code',
        'slug',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => 'Unit baru telah dibuat',
                'updated' => 'Unit telah diperbarui',
                'deleted' => 'Unit telah dihapus',
                default => "Unit {$eventName}",
            });
    }

    protected $fillable = [
        'course_id', 'code', 'slug', 'title', 'description',
        'order', 'status',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(\Modules\Schemes\Models\Course::class);
    }

    public function lessons()
    {
        return $this->hasMany(\Modules\Schemes\Models\Lesson::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }



    protected static function newFactory()
    {
        return \Modules\Schemes\Database\Factories\UnitFactory::new();
    }
}
