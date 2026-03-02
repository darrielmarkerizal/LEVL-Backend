<?php

declare(strict_types=1);

namespace Modules\Learning\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Common\Traits\PgSearchable;
use Modules\Learning\Enums\AssignmentStatus;
use Modules\Learning\Enums\AssignmentType;
use Modules\Learning\Enums\RandomizationType;
use Modules\Learning\Enums\ReviewMode;
use Modules\Learning\Enums\SubmissionType;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Assignment extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, PgSearchable;

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
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/zip',
                'image/jpeg',
                'image/png',
                'image/webp',
            ]);
    }

    protected $fillable = [
        'lesson_id',
        'assignable_type',
        'assignable_id',
        'created_by',
        'title',
        'description',
        'type',
        'submission_type',
        'max_score',
        'passing_grade',
        'max_attempts',
        'cooldown_minutes',
        'retake_enabled',
        'review_mode',
        'randomization_type',
        'question_bank_count',
        'status',
        'allow_resubmit',
        'time_limit_minutes',
        'allow_multiple',
    ];

    protected $casts = [
        'type' => AssignmentType::class,
        'submission_type' => SubmissionType::class,
        'status' => AssignmentStatus::class,
        'review_mode' => ReviewMode::class,
        'randomization_type' => RandomizationType::class,
        'passing_grade' => 'decimal:2',
        'max_attempts' => 'integer',
        'cooldown_minutes' => 'integer',
        'question_bank_count' => 'integer',
        'allow_resubmit' => 'boolean',
        'retake_enabled' => 'boolean',
        'allow_multiple' => 'boolean',
    ];

    protected static function newFactory()
    {
        return \Modules\Learning\Database\Factories\AssignmentFactory::new();
    }

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

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->ordered();
    }

    public function overrides(): HasMany
    {
        return $this->hasMany(Override::class);
    }

    public function activeOverrides(): HasMany
    {
        return $this->hasMany(Override::class)->active();
    }

    public function isAvailable(): bool
    {
        return $this->status === AssignmentStatus::Published;
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

    public function scopeForLesson($query, int $lessonId)
    {
        return $query->where(function ($q) use ($lessonId) {
            $q->where('assignable_type', \Modules\Schemes\Models\Lesson::class)
                ->where('assignable_id', $lessonId);
        })->orWhere('lesson_id', $lessonId);
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

    public function scopePublished($query, bool $isPublished = true)
    {
        if ($isPublished) {
            return $query->where('status', AssignmentStatus::Published);
        }

        return $query->where('status', '!=', AssignmentStatus::Published);
    }

    public function scopeAvailable($query, bool $isAvailable = true)
    {
        if ($isAvailable) {
            return $query->published();
        }

        return $query->where('status', '!=', AssignmentStatus::Published);
    }

    public function hasValidScope(): bool
    {
        $hasPolymorphic = $this->assignable_type && $this->assignable_id;
        $hasLegacy = (bool) $this->lesson_id;

        return $hasPolymorphic xor $hasLegacy;
    }

    public function getCourseId(): ?int
    {

        if ($this->assignable_type === \Modules\Schemes\Models\Course::class) {
            return $this->assignable_id;
        }

        if ($this->assignable_type === \Modules\Schemes\Models\Unit::class) {
            $unit = $this->assignable;

            return $unit?->course_id;
        }

        if ($this->assignable_type === \Modules\Schemes\Models\Lesson::class) {
            $lesson = $this->assignable;

            return $lesson?->unit?->course_id;
        }

        if ($this->lesson_id) {
            $this->loadMissing('lesson.unit');

            return $this->lesson?->unit?->course_id;
        }

        return null;
    }

    /**
     * Check if this is an assignment (file upload type)
     */
    public function isAssignment(): bool
    {
        return $this->type === AssignmentType::Assignment;
    }

    /**
     * Check if this is a quiz (questions type)
     */
    public function isQuiz(): bool
    {
        return $this->type === AssignmentType::Quiz;
    }

    /**
     * Scope to filter by assignment type
     */
    public function scopeOfType($query, AssignmentType $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get only assignments (file upload)
     */
    public function scopeAssignments($query)
    {
        return $query->where('type', AssignmentType::Assignment);
    }

    /**
     * Scope to get only quizzes (questions)
     */
    public function scopeQuizzes($query)
    {
        return $query->where('type', AssignmentType::Quiz);
    }
}
