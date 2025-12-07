<?php

namespace Modules\Schemes\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Modules\Schemes\Enums\ContentType;

class Lesson extends Model
{
    use HasFactory, Searchable;

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
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function blocks()
    {
        return $this->hasMany(LessonBlock::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing(['unit', 'unit.course']);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'markdown_content' => $this->markdown_content,
            'unit_id' => $this->unit_id,
            'unit_title' => $this->unit?->title,
            'course_id' => $this->unit?->course_id,
            'course_title' => $this->unit?->course?->title,
            'status' => $this->status,
            'content_type' => $this->content_type?->value,
        ];
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'lessons_index';
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\LessonFactory::new();
    }
}
