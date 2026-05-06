<?php

declare(strict_types=1);

namespace Modules\Schemes\Services\Support;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Enrollments\Enums\EnrollmentStatus;
use Modules\Enrollments\Enums\ProgressStatus;
use Modules\Enrollments\Models\CourseProgress;
use Modules\Enrollments\Models\Enrollment;
use Modules\Enrollments\Models\LessonProgress;
use Modules\Enrollments\Models\UnitProgress;
use Modules\Learning\Enums\AssignmentStatus;
use Modules\Learning\Enums\QuizStatus;
use Modules\Learning\Enums\SubmissionStatus;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Quiz;
use Modules\Learning\Models\QuizSubmission;
use Modules\Learning\Models\Submission;
use Modules\Schemes\Events\CourseCompleted;
use Modules\Schemes\Events\UnitCompleted;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\Unit;

class ProgressionStateProcessor
{
    public function markLessonCompleted(Lesson $lesson, Enrollment $enrollment): void
    {
        DB::transaction(function () use ($lesson, $enrollment) {
            $lockedEnrollment = Enrollment::lockForUpdate()->findOrFail($enrollment->id);

            $alreadyCompleted = LessonProgress::where('enrollment_id', $lockedEnrollment->id)
                ->where('lesson_id', $lesson->id)
                ->where('status', ProgressStatus::Completed)
                ->exists();

            $lesson->load([
                'unit.course',
                'unit.lessons' => function ($query) {
                    $query->where('status', 'published')->orderBy('order');
                },
            ]);
            $lessonModel = $lesson;

            if (! $lessonModel->unit || ! $lessonModel->unit->course) {
                return;
            }

            if (! $alreadyCompleted) {
                $this->storeLessonCompletion($lessonModel, $lockedEnrollment);
            }

            $unitResult = $this->updateUnitProgress(
                $lessonModel->unit,
                $lockedEnrollment,
                $lessonModel->unit->lessons
            );

            $this->updateCourseProgress($lessonModel->unit->course, $lockedEnrollment);

            DB::afterCommit(function () use ($lessonModel, $lockedEnrollment, $unitResult, $alreadyCompleted) {
                if (! $alreadyCompleted) {
                    \Modules\Schemes\Events\LessonCompleted::dispatch($lessonModel, $lockedEnrollment->user_id, $lockedEnrollment->id);

                    if ($unitResult['just_completed']) {
                        UnitCompleted::dispatch($lessonModel->unit, $lockedEnrollment->user_id, $lockedEnrollment->id);
                    }
                }
            });
        });
    }

    public function markLessonUncompleted(Lesson $lesson, Enrollment $enrollment): void
    {
        DB::transaction(function () use ($lesson, $enrollment) {
            $lesson->load([
                'unit.course',
                'unit.lessons' => function ($query) {
                    $query->where('status', 'published')->orderBy('order');
                },
            ]);
            $lessonModel = $lesson;

            if (! $lessonModel->unit || ! $lessonModel->unit->course) {
                return;
            }

            $progress = LessonProgress::query()
                ->where('enrollment_id', $enrollment->id)
                ->where('lesson_id', $lessonModel->id)
                ->first();

            if ($progress) {
                $progress->status = ProgressStatus::NotStarted;
                $progress->progress_percent = 0;
                $progress->completed_at = null;
                $progress->save();
            }

            $this->updateUnitProgress(
                $lessonModel->unit,
                $enrollment,
                $lessonModel->unit->lessons
            );

            $this->updateCourseProgress($lessonModel->unit->course, $enrollment);
        });
    }

    public function markUnitCompleted(Unit $unit, Enrollment $enrollment): void
    {
        DB::transaction(function () use ($unit, $enrollment) {
            $unit->load([
                'course',
                'lessons' => function ($query) {
                    $query->where('status', 'published')->orderBy('order');
                },
            ]);
            $unitModel = $unit;

            if (! $unitModel->course) {
                return;
            }

            $this->updateUnitProgress($unitModel, $enrollment, $unitModel->lessons, true);
            $this->updateCourseProgress($unitModel->course, $enrollment);
        });
    }

    
    public function refreshUnitAndCourseProgress(Unit $unit, Enrollment $enrollment): void
    {
        DB::transaction(function () use ($unit, $enrollment) {
            $unit->loadMissing([
                'course',
                'lessons' => function ($query) {
                    $query->where('status', 'published')->orderBy('order');
                },
            ]);

            if (! $unit->course) {
                return;
            }

            $unitResult = $this->updateUnitProgress($unit, $enrollment, $unit->lessons);
            $this->updateCourseProgress($unit->course, $enrollment);

            DB::afterCommit(function () use ($unit, $enrollment, $unitResult) {
                if ($unitResult['just_completed']) {
                    \Modules\Schemes\Events\UnitCompleted::dispatch($unit, $enrollment->user_id, $enrollment->id);
                }
            });
        });
    }

    public function getCourseProgressData(Course $course, Enrollment $enrollment): array
    {
        $course->load([
            'units' => function ($query) {
                $query->where('status', 'published')
                    ->orderBy('order')
                    ->with([
                        'lessons' => function ($lessonQuery) {
                            $lessonQuery->where('status', 'published')->orderBy('order');
                        },
                        'quizzes' => function ($quizQuery) {
                            $quizQuery->where('status', QuizStatus::Published)->orderBy('order');
                        },
                        'assignments' => function ($assignmentQuery) {
                            $assignmentQuery->where('status', AssignmentStatus::Published)->orderBy('order');
                        },
                    ]);
            },
        ]);
        $courseModel = $course;

        DB::transaction(function () use ($courseModel, $enrollment) {
            foreach ($courseModel->units as $unit) {
                $this->updateUnitProgress($unit, $enrollment, $unit->lessons);
            }
            $this->updateCourseProgress($courseModel, $enrollment);
        });

        $unitIds = $courseModel->units->pluck('id');
        $lessonIds = $courseModel->units
            ->flatMap(fn ($unit) => $unit->lessons->pluck('id'))
            ->values();

        $lessonProgressMap = LessonProgress::query()
            ->where('enrollment_id', $enrollment->id)
            ->whereIn('lesson_id', $lessonIds)
            ->get()
            ->keyBy('lesson_id');

        $unitProgressMap = UnitProgress::query()
            ->where('enrollment_id', $enrollment->id)
            ->whereIn('unit_id', $unitIds)
            ->get()
            ->keyBy('unit_id');

        $courseProgress = CourseProgress::query()
            ->where('enrollment_id', $enrollment->id)
            ->first();

        $userId = $enrollment->user_id;

        $unitsData = [];
        $previousUnitsCompleted = true;
        $totalCourseItems = 0;
        $completedCourseItems = 0;

        foreach ($courseModel->units as $unit) {
            $lessons = $unit->lessons ?? new EloquentCollection;
            $quizzes = $unit->quizzes ?? new EloquentCollection;
            $assignments = $unit->assignments ?? new EloquentCollection;

            $unitProgress = $unitProgressMap->get($unit->id);
            $elementsData = [];

            $allElements = collect();

            foreach ($lessons as $lesson) {
                $allElements->push([
                    'type' => 'lesson',
                    'order' => $lesson->order,
                    'model' => $lesson,
                ]);
            }

            foreach ($quizzes as $quiz) {
                $allElements->push([
                    'type' => 'quiz',
                    'order' => $quiz->order,
                    'model' => $quiz,
                ]);
            }

            foreach ($assignments as $assignment) {
                $allElements->push([
                    'type' => 'assignment',
                    'order' => $assignment->order,
                    'model' => $assignment,
                ]);
            }

            $totalUnitItems = $allElements->count();
            $completedUnitItems = 0;
            $previousElementCompleted = true;

            foreach ($allElements as $element) {
                $type = $element['type'];
                $model = $element['model'];
                $isCompleted = false;
                $elementStatus = ProgressStatus::NotStarted;
                $elementPercent = 0;
                $completedAt = null;

                if ($type === 'lesson') {
                    $lessonProgress = $lessonProgressMap->get($model->id);
                    $elementStatus = $lessonProgress->status ?? ProgressStatus::NotStarted;
                    $elementPercent = $lessonProgress->progress_percent ?? 0;
                    $isCompleted = $elementStatus === ProgressStatus::Completed;
                    $completedAt = optional($lessonProgress?->completed_at)->toIso8601String();
                } elseif ($type === 'quiz') {
                    $quizSubmission = QuizSubmission::where('user_id', $userId)
                        ->where('quiz_id', $model->id)
                        ->whereIn('status', ['graded', 'released'])
                        ->whereNotNull('final_score')
                        ->whereRaw('final_score >= (select passing_grade from quizzes where quizzes.id = quiz_submissions.quiz_id)')
                        ->latest('submitted_at')
                        ->first();

                    if ($quizSubmission) {
                        $elementStatus = ProgressStatus::Completed;
                        $elementPercent = 100;
                        $isCompleted = true;
                        $completedAt = optional($quizSubmission->submitted_at)->toIso8601String();
                    } else {
                        $hasAttempt = QuizSubmission::where('user_id', $userId)
                            ->where('quiz_id', $model->id)
                            ->exists();
                        $elementStatus = $hasAttempt ? ProgressStatus::InProgress : ProgressStatus::NotStarted;
                        $elementPercent = 0;
                    }
                } elseif ($type === 'assignment') {
                    $submission = Submission::where('user_id', $userId)
                        ->where('assignment_id', $model->id)
                        ->where('status', SubmissionStatus::Graded)
                        ->whereRaw('score >= (select COALESCE(passing_grade, max_score * 0.6) from assignments where assignments.id = submissions.assignment_id)')
                        ->latest('submitted_at')
                        ->first();

                    if ($submission) {
                        $elementStatus = ProgressStatus::Completed;
                        $elementPercent = 100;
                        $isCompleted = true;
                        $completedAt = optional($submission->submitted_at)->toIso8601String();
                    } else {
                        $hasSubmission = Submission::where('user_id', $userId)
                            ->where('assignment_id', $model->id)
                            ->exists();
                        $elementStatus = $hasSubmission ? ProgressStatus::InProgress : ProgressStatus::NotStarted;
                        $elementPercent = 0;
                    }
                }

                if ($isCompleted) {
                    $completedUnitItems++;
                    $completedCourseItems++;
                }
                $totalCourseItems++;

                $isElementLocked = ! $previousUnitsCompleted || ! $previousElementCompleted;

                $elementsData[] = [
                    'id' => $model->id,
                    'slug' => $model->slug ?? null,
                    'title' => $model->title,
                    'order' => $model->order,
                    'type' => $type,
                    'status' => $elementStatus instanceof ProgressStatus ? $elementStatus->value : $elementStatus,
                    'progress_percent' => round((float) $elementPercent, 2),
                    'is_locked' => $isElementLocked,
                    'completed_at' => $completedAt,
                ];

                if (!$isCompleted) {
                    $previousElementCompleted = false;
                }
            }

            // Calculate unit progress percentage
            $unitPercent = $totalUnitItems > 0
                ? round(($completedUnitItems / $totalUnitItems) * 100, 2)
                : 0;

            // Determine unit status
            $unitStatus = $unitProgress?->status ?? (
                $totalUnitItems === 0 ? ProgressStatus::NotStarted :
                ($completedUnitItems === $totalUnitItems ? ProgressStatus::Completed :
                    ($completedUnitItems > 0 ? ProgressStatus::InProgress : ProgressStatus::NotStarted))
            );

            $displayUnitPercent = $unitPercent;
            if ($unitProgress) {
                $unitProgress->update(['progress_percent' => $displayUnitPercent]);
            }

            $isUnitLocked = ! $previousUnitsCompleted;

            if ($unitStatus === ProgressStatus::Completed) {
                $previousUnitsCompleted = true;
            } else {
                $previousUnitsCompleted = false;
            }

            $unitsData[] = [
                'id' => $unit->id,
                'slug' => $unit->slug,
                'title' => $unit->title,
                'order' => $unit->order,
                'status' => $unitStatus instanceof ProgressStatus ? $unitStatus->value : $unitStatus,
                'progress_percent' => round((float) $displayUnitPercent, 2),
                'is_locked' => $isUnitLocked,
                'completed_at' => optional($unitProgress?->completed_at)->toIso8601String(),
                'elements' => $elementsData,
            ];
        }

        // Calculate course progress based on total items across all units
        $coursePercent = $totalCourseItems > 0
            ? round(($completedCourseItems / $totalCourseItems) * 100, 2)
            : 0;

        // Determine course status
        $courseStatus = $courseProgress?->status ?? (
            $totalCourseItems === 0 ? ProgressStatus::NotStarted :
            ($completedCourseItems === $totalCourseItems ? ProgressStatus::Completed :
                ($completedCourseItems > 0 ? ProgressStatus::InProgress : ProgressStatus::NotStarted))
        );

        // Always use calculated progress for accuracy, sync to DB if needed
        $displayCoursePercent = $coursePercent;
        if ($courseProgress) {
            $courseProgress->update(['progress_percent' => $displayCoursePercent]);
        }

        return [
            'course' => [
                'id' => $courseModel->id,
                'slug' => $courseModel->slug,
                'title' => $courseModel->title,
                'status' => $courseStatus instanceof ProgressStatus ? $courseStatus->value : $courseStatus,
                'progress_percent' => round((float) $displayCoursePercent, 2),
                'completed_at' => optional($courseProgress?->completed_at)->toIso8601String(),
            ],
            'units' => $unitsData,
        ];
    }

    private function storeLessonCompletion(Lesson $lesson, Enrollment $enrollment): void
    {
        $progress = LessonProgress::query()
            ->firstOrNew([
                'enrollment_id' => $enrollment->id,
                'lesson_id' => $lesson->id,
            ]);

        if (! $progress->started_at) {
            $progress->started_at = Carbon::now();
        }

        $progress->status = ProgressStatus::Completed;
        $progress->progress_percent = 100;
        $progress->completed_at = Carbon::now();
        $progress->save();
    }

    private function updateUnitProgress(
        Unit $unit,
        Enrollment $enrollment,
        ?EloquentCollection $lessons = null,
        bool $forceComplete = false
    ): array {
        $lessonsCollection = $lessons ?? $unit->lessons()
            ->where('status', 'published')
            ->orderBy('order')
            ->get();

        $lessonIds = $lessonsCollection->pluck('id');
        $totalLessons = $lessonIds->count();

        
        $quizIds = Quiz::where('unit_id', $unit->id)
            ->where('status', QuizStatus::Published)
            ->pluck('id');

        $assignmentIds = Assignment::where('unit_id', $unit->id)
            ->where('status', AssignmentStatus::Published)
            ->pluck('id');

        $totalItems = $totalLessons + $quizIds->count() + $assignmentIds->count();
        $userId = $enrollment->user_id;

        if ($totalItems === 0) {
            $status = ProgressStatus::Completed;
            $progressPercent = 100;
        } else {
            $completedLessons = $totalLessons > 0
                ? LessonProgress::query()
                    ->where('enrollment_id', $enrollment->id)
                    ->whereIn('lesson_id', $lessonIds)
                    ->where('status', ProgressStatus::Completed->value)
                    ->count()
                : 0;

            $completedQuizzes = $quizIds->isNotEmpty()
                ? QuizSubmission::where('user_id', $userId)
                    ->whereIn('quiz_id', $quizIds)
                    ->whereIn('status', ['graded', 'released'])
                    ->whereNotNull('final_score')
                    ->whereRaw('final_score >= (select passing_grade from quizzes where quizzes.id = quiz_submissions.quiz_id)')
                    ->distinct('quiz_id')
                    ->count('quiz_id')
                : 0;

            $completedAssignments = $assignmentIds->isNotEmpty()
                ? Submission::where('user_id', $userId)
                    ->whereIn('assignment_id', $assignmentIds)
                    ->where('status', SubmissionStatus::Graded)
                    ->whereRaw('score >= (select COALESCE(passing_grade, max_score * 0.6) from assignments where assignments.id = submissions.assignment_id)')
                    ->distinct('assignment_id')
                    ->count('assignment_id')
                : 0;

            $completedItems = $completedLessons + $completedQuizzes + $completedAssignments;

            $hasProgress = $completedItems > 0
                || ($totalLessons > 0 && LessonProgress::query()
                    ->where('enrollment_id', $enrollment->id)
                    ->whereIn('lesson_id', $lessonIds)
                    ->whereIn('status', [ProgressStatus::InProgress->value, ProgressStatus::Completed->value])
                    ->exists());

            if ($forceComplete || $completedItems === $totalItems) {
                $status = ProgressStatus::Completed;
                $progressPercent = 100;
            } elseif ($hasProgress) {
                $status = ProgressStatus::InProgress;
                $progressPercent = round(($completedItems / $totalItems) * 100, 2);
            } else {
                $status = ProgressStatus::NotStarted;
                $progressPercent = 0;
            }
        }

        $progress = UnitProgress::query()
            ->firstOrNew([
                'enrollment_id' => $enrollment->id,
                'unit_id' => $unit->id,
            ]);

        $previousStatus = $progress->exists ? $progress->status : ProgressStatus::NotStarted;

        $progress->status = $status;
        $progress->progress_percent = $progressPercent;

        if ($status !== ProgressStatus::NotStarted && ! $progress->started_at) {
            $progress->started_at = Carbon::now();
        }

        if ($status === ProgressStatus::Completed && ! $progress->completed_at) {
            $progress->completed_at = Carbon::now();
        }

        if ($status !== ProgressStatus::Completed) {
            $progress->completed_at = null;
        }

        $progress->save();

        return [
            'status' => $status,
            'progress_percent' => $progressPercent,
            'just_completed' => $previousStatus !== ProgressStatus::Completed && $status === ProgressStatus::Completed,
        ];
    }

    private function updateCourseProgress(Course $course, Enrollment $enrollment): array
    {
        $unitIds = $course->units()
            ->where('status', 'published')
            ->orderBy('order')
            ->pluck('id');

        $totalUnits = $unitIds->count();

        if ($totalUnits === 0) {
            $status = ProgressStatus::Completed;
            $progressPercent = 100;
        } else {
            $completedUnits = UnitProgress::query()
                ->where('enrollment_id', $enrollment->id)
                ->whereIn('unit_id', $unitIds)
                ->where('status', ProgressStatus::Completed->value)
                ->count();

            $hasProgress = UnitProgress::query()
                ->where('enrollment_id', $enrollment->id)
                ->whereIn('unit_id', $unitIds)
                ->whereIn('status', [ProgressStatus::InProgress->value, ProgressStatus::Completed->value])
                ->exists();

            if ($completedUnits === $totalUnits) {
                $status = ProgressStatus::Completed;
                $progressPercent = 100;
            } elseif ($hasProgress || $completedUnits > 0) {
                $status = ProgressStatus::InProgress;
                $progressPercent = round(($completedUnits / $totalUnits) * 100, 2);
            } else {
                $status = ProgressStatus::NotStarted;
                $progressPercent = 0;
            }
        }

        $progress = CourseProgress::query()
            ->firstOrNew([
                'enrollment_id' => $enrollment->id,
            ]);

        $previousStatus = $progress->exists ? $progress->status : ProgressStatus::NotStarted;

        $progress->status = $status;
        $progress->progress_percent = $progressPercent;

        if ($status !== ProgressStatus::NotStarted && ! $progress->started_at) {
            $progress->started_at = Carbon::now();
        }

        if ($status === ProgressStatus::Completed && ! $progress->completed_at) {
            $progress->completed_at = Carbon::now();
        }

        if ($status !== ProgressStatus::Completed) {
            $progress->completed_at = null;
        }

        $progress->save();

        $courseJustCompleted = $previousStatus !== ProgressStatus::Completed && $status === ProgressStatus::Completed;

        if ($status === ProgressStatus::Completed) {
            $enrollment->completed_at = $enrollment->completed_at ?? Carbon::now();
            $enrollment->status = EnrollmentStatus::Completed;
        } elseif ($enrollment->status === EnrollmentStatus::Completed && $status !== ProgressStatus::Completed) {
            $enrollment->status = EnrollmentStatus::Active;
            $enrollment->completed_at = null;
        }
        $enrollment->save();

        if ($courseJustCompleted) {
            CourseCompleted::dispatch($course, $enrollment);
        }

        return [
            'status' => $status,
            'progress_percent' => $progressPercent,
            'just_completed' => $courseJustCompleted,
        ];
    }
}
