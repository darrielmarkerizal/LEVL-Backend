<?php

declare(strict_types=1);

namespace Modules\Learning\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    use HasFactory, InteractsWithMedia, PgSearchable, \Modules\Common\Traits\PublishedOnlyScope;

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
        'unit_id',
        'order',
        'created_by',
        'title',
        'description',
        'type',
        'submission_type',
        'max_score',
        'passing_grade',
        'review_mode',
        'randomization_type',
        'question_bank_count',
        'status',
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
        'question_bank_count' => 'integer',
        'order' => 'integer',
        'allow_multiple' => 'boolean',
    ];

    protected static function newFactory()
    {
        return \Modules\Learning\Database\Factories\AssignmentFactory::new();
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(\Modules\Schemes\Models\Unit::class);
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

    public function isAvailable(): bool
    {
        return $this->status === AssignmentStatus::Published;
    }

    public function getScopeTypeAttribute(): string
    {
        return 'unit';
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeForUnit($query, int $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    public function scopeForCourse($query, int $courseId)
    {
        return $query->whereHas('unit', function ($q) use ($courseId) {
            $q->where('course_id', $courseId);
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

    public function getCourseId(): ?int
    {
        $this->loadMissing('unit');

        return $this->unit?->course_id;
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
