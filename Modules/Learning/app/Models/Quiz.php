<?php

declare(strict_types=1);

namespace Modules\Learning\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Common\Traits\PgSearchable;
use Modules\Learning\Enums\QuizStatus;
use Modules\Learning\Enums\RandomizationType;
use Modules\Learning\Enums\ReviewMode;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Quiz extends Model implements HasMedia
{
    use InteractsWithMedia, PgSearchable;

    protected array $searchable_columns = [
        'title',
        'description',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->useDisk('do')
            ->acceptsMimeTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png',
                'image/webp',
            ]);
    }

    protected $fillable = [
        'assignable_type',
        'assignable_id',
        'lesson_id',
        'created_by',
        'title',
        'description',
        'passing_grade',
        'auto_grading',
        'max_score',
        'max_attempts',
        'cooldown_minutes',
        'time_limit_minutes',
        'retake_enabled',
        'randomization_type',
        'question_bank_count',
        'review_mode',
        'status',
    ];

    protected $casts = [
        'status' => QuizStatus::class,
        'review_mode' => ReviewMode::class,
        'randomization_type' => RandomizationType::class,
        'passing_grade' => 'decimal:2',
        'max_score' => 'decimal:2',
        'max_attempts' => 'integer',
        'cooldown_minutes' => 'integer',
        'time_limit_minutes' => 'integer',
        'question_bank_count' => 'integer',
        'auto_grading' => 'boolean',
        'retake_enabled' => 'boolean',
    ];

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(\Modules\Schemes\Models\Lesson::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\Modules\Auth\Models\User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(QuizSubmission::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === QuizStatus::Published;
    }

    public function hasOnlyObjectiveQuestions(): bool
    {
        return $this->questions()->where('type', 'essay')->doesntExist();
    }

    public function hasEssayQuestions(): bool
    {
        return $this->questions()->where('type', 'essay')->exists();
    }

    public function hasOnlyEssayQuestions(): bool
    {
        return $this->questions()->whereNotIn('type', ['essay'])->doesntExist();
    }

    public function hasObjectiveAndEssay(): bool
    {
        return $this->hasEssayQuestions() && ! $this->hasOnlyEssayQuestions();
    }

    public function getScopeTypeAttribute(): ?string
    {
        if ($this->assignable_type) {
            return match ($this->assignable_type) {
                \Modules\Schemes\Models\Lesson::class => 'lesson',
                \Modules\Schemes\Models\Unit::class => 'unit',
                \Modules\Schemes\Models\Course::class => 'course',
                default => null,
            };
        }

        if ($this->lesson_id) {
            return 'lesson';
        }

        return null;
    }

    public function getCourseId(): ?int
    {
        if ($this->assignable_type === \Modules\Schemes\Models\Course::class) {
            return $this->assignable_id;
        }

        if ($this->assignable_type === \Modules\Schemes\Models\Unit::class) {
            return $this->assignable?->course_id;
        }

        if ($this->assignable_type === \Modules\Schemes\Models\Lesson::class) {
            return $this->assignable?->unit?->course_id;
        }

        if ($this->lesson_id) {
            $this->loadMissing('lesson.unit');

            return $this->lesson?->unit?->course_id;
        }

        return null;
    }

    public function scopePublished($query, bool $isPublished = true)
    {
        if ($isPublished) {
            return $query->where('status', QuizStatus::Published);
        }

        return $query->where('status', '!=', QuizStatus::Published);
    }

    public function scopeAvailable($query, bool $isAvailable = true)
    {
        if ($isAvailable) {
            return $query->published();
        }

        return $query->where('status', '!=', QuizStatus::Published);
    }

    public function scopeForUnit($query, int $unitId)
    {
        $lessonIds = \Modules\Schemes\Models\Lesson::where('unit_id', $unitId)->pluck('id')->toArray();

        return $query->where(function ($q) use ($unitId, $lessonIds) {
            $q->where(function ($subQ) use ($unitId) {
                $subQ->where('assignable_type', \Modules\Schemes\Models\Unit::class)
                    ->where('assignable_id', $unitId);
            })
                ->orWhere(function ($subQ) use ($lessonIds) {
                    $subQ->where('assignable_type', \Modules\Schemes\Models\Lesson::class)
                        ->whereIn('assignable_id', $lessonIds);
                })
                ->orWhereIn('lesson_id', $lessonIds);
        });
    }

    public function scopeForCourse($query, int $courseId)
    {
        $unitIds = \Modules\Schemes\Models\Unit::where('course_id', $courseId)->pluck('id')->toArray();
        $lessonIds = \Modules\Schemes\Models\Lesson::whereIn('unit_id', $unitIds)->pluck('id')->toArray();

        return $query->where(function ($q) use ($courseId, $unitIds, $lessonIds) {
            $q->where(function ($subQ) use ($courseId) {
                $subQ->where('assignable_type', \Modules\Schemes\Models\Course::class)
                    ->where('assignable_id', $courseId);
            })
                ->orWhere(function ($subQ) use ($unitIds) {
                    $subQ->where('assignable_type', \Modules\Schemes\Models\Unit::class)
                        ->whereIn('assignable_id', $unitIds);
                })
                ->orWhere(function ($subQ) use ($lessonIds) {
                    $subQ->where('assignable_type', \Modules\Schemes\Models\Lesson::class)
                        ->whereIn('assignable_id', $lessonIds);
                })
                ->orWhereIn('lesson_id', $lessonIds);
        });
    }
}
