<?php

declare(strict_types=1);

namespace Modules\Enrollments\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Common\Traits\PgSearchable;
use Modules\Enrollments\Enums\EnrollmentStatus;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Enrollment extends Model
{
    use HasFactory, LogsActivity, PgSearchable;

    protected array $searchable_columns = [
        
        
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->dontLogIfAttributesChangedOnly(['created_at', 'updated_at'])
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => 'Enrollment baru telah dibuat',
                'updated' => 'Enrollment telah diperbarui',
                'deleted' => 'Enrollment telah dihapus',
                default => "Enrollment {$eventName}",
            });
    }

    protected static function boot()
    {
        parent::boot();
    }

    protected $fillable = [
        'user_id', 'course_id', 'status',
        'enrolled_at', 'auto_activate_on_enrolled_at', 'completed_at',
    ];

    protected $casts = [
        'status' => EnrollmentStatus::class,
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $hidden = [
        'course_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(\Modules\Auth\Models\User::class);
    }

    public function course()
    {
        return $this->belongsTo(\Modules\Schemes\Models\Course::class);
    }

    public function unitProgress()
    {
        return $this->hasMany(UnitProgress::class);
    }

    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function courseProgress()
    {
        return $this->hasOne(CourseProgress::class);
    }

    public function activities()
    {
        return $this->hasMany(EnrollmentActivity::class);
    }

    public function assignmentSubmissions()
    {
        return $this->hasMany(\Modules\Learning\Models\Submission::class, 'enrollment_id');
    }

    public function quizSubmissions()
    {
        return $this->hasMany(\Modules\Learning\Models\QuizSubmission::class, 'enrollment_id');
    }

    protected static function newFactory()
    {
        return \Modules\Enrollments\Database\Factories\EnrollmentFactory::new();
    }

    protected function autoActivateOnEnrolledAt(): Attribute
    {
        return Attribute::make(
            get: static fn (mixed $value): bool => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            set: static fn (mixed $value): string => filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false',
        );
    }
}
