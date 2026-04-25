<?php

declare(strict_types=1);

namespace Modules\Schemes\Models;

use App\Models\Concerns\TracksTrashBin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Common\Traits\PgSearchable;
use Modules\Schemes\Enums\ContentType;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Lesson extends Model
{
    use HasFactory, HasSlug, LogsActivity, \Modules\Common\Traits\PublishedOnlyScope, PgSearchable, SoftDeletes, TracksTrashBin;

    protected array $searchable_columns = [
        'title',
        'description',
        'markdown_content',
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
                'created' => 'Lesson baru telah dibuat',
                'updated' => 'Lesson telah diperbarui',
                'deleted' => 'Lesson telah dihapus',
                default => "Lesson {$eventName}",
            });
    }

    protected $fillable = [
        'unit_id', 'slug', 'title', 'description',
        'markdown_content', 'content_type', 'content_url',
        'order', 'duration_minutes', 'status', 'published_at',
    ];

    protected $casts = [
        'order' => 'integer',
        'duration_minutes' => 'integer',
        'published_at' => 'datetime',
        'content_type' => ContentType::class,
        'status' => \Modules\Schemes\Enums\PublishStatus::class,
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function blocks()
    {
        return $this->hasMany(LessonBlock::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(\Modules\Enrollments\Models\LessonProgress::class);
    }

    public function unitContent(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(UnitContent::class, 'contentable');
    }

    public function isCompletedBy(int $userId): bool
    {
        
        return \DB::table('lesson_progress')
            ->join('enrollments', 'lesson_progress.enrollment_id', '=', 'enrollments.id')
            ->where('lesson_progress.lesson_id', $this->id)
            ->where('enrollments.user_id', $userId)
            ->where('lesson_progress.status', 'completed')
            ->exists();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function newFactory()
    {
        return \Modules\Schemes\Database\Factories\LessonFactory::new();
    }
}
