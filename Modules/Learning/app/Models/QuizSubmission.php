<?php

declare(strict_types=1);

namespace Modules\Learning\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Learning\Enums\QuizGradingStatus;
use Modules\Learning\Enums\QuizSubmissionStatus;

class QuizSubmission extends Model
{
    protected $table = 'quiz_submissions';

    protected $fillable = [
        'quiz_id',
        'user_id',
        'enrollment_id',
        'status',
        'grading_status',
        'score',
        'final_score',
        'question_set',
        'submitted_at',
        'started_at',
        'time_spent_seconds',
    ];

    protected $casts = [
        'status' => QuizSubmissionStatus::class,
        'grading_status' => QuizGradingStatus::class,
        'submitted_at' => 'datetime',
        'started_at' => 'datetime',
        'time_spent_seconds' => 'integer',
        'score' => 'decimal:2',
        'final_score' => 'decimal:2',
        'question_set' => 'array',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Auth\Models\User::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(\Modules\Enrollments\Models\Enrollment::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class, 'quiz_submission_id');
    }

    public function isPassed(): bool
    {
        $finalScore = $this->final_score ?? $this->score;
        if ($finalScore === null) {
            return false;
        }

        return (float) $finalScore >= (float) $this->quiz->passing_grade;
    }

    public function getDurationAttribute(): ?int
    {
        if (! $this->submitted_at || ! $this->started_at) {
            return null;
        }

        return (int) $this->started_at->diffInSeconds($this->submitted_at);
    }

    public function scopeForStudent($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForQuiz($query, int $quizId)
    {
        return $query->where('quiz_id', $quizId);
    }

    public function scopeWithStatus($query, QuizSubmissionStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeWithGradingStatus($query, QuizGradingStatus $gradingStatus)
    {
        return $query->where('grading_status', $gradingStatus);
    }

    public function scopeNeedsManualGrading($query)
    {
        return $query->whereIn('grading_status', [
            QuizGradingStatus::WaitingForGrading->value,
            QuizGradingStatus::PartiallyGraded->value,
        ]);
    }

    public function scopeHighestScore($query)
    {
        return $query->orderByDesc('final_score');
    }
}
