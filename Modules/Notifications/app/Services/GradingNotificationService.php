<?php

declare(strict_types=1);

namespace Modules\Notifications\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Models\User;
use Modules\Learning\Models\Submission;
use Modules\Notifications\Contracts\Services\GradingNotificationServiceInterface;
use Modules\Notifications\Models\NotificationPreference;

class GradingNotificationService implements GradingNotificationServiceInterface
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function notifySubmissionGraded(Submission $submission): void
    {
        $submission->loadMissing(['user', 'assignment']);
        if (! $submission->user) {
            return;
        }

        $assignmentTitle = $submission->assignment?->title ?? __('notifications.assignment');
        $title = __('notifications.grading.submission_graded_title');
        $message = __('notifications.grading.submission_graded_message', [
            'assignment' => $assignmentTitle,
        ]);
        $data = [
            'submission_id' => $submission->id,
            'assignment_id' => $submission->assignment_id,
            'score' => $submission->score,
        ];

        $this->sendToSupportedChannels($submission->user, $title, $message, $data);

        Log::info(__('notifications.grading.submission_graded', [
            'id' => $submission->id,
            'user_id' => $submission->user_id,
        ]));
    }

    public function notifyGradesReleased(Collection $submissions): void
    {
        $submissions->loadMissing(['user', 'assignment']);
        foreach ($submissions as $submission) {
            if (! $submission->user) {
                continue;
            }

            $assignmentTitle = $submission->assignment?->title ?? __('notifications.assignment');
            $title = __('notifications.grading.grades_released_title');
            $message = __('notifications.grading.grades_released_message', [
                'assignment' => $assignmentTitle,
            ]);
            $data = [
                'submission_id' => $submission->id,
                'assignment_id' => $submission->assignment_id,
                'score' => $submission->score,
            ];

            $this->sendToSupportedChannels($submission->user, $title, $message, $data);
        }

        Log::info(__('notifications.grading.grades_released', [
            'count' => $submissions->count(),
        ]));
    }

    public function notifyManualGradingRequired(Submission $submission): void
    {
        $submission->loadMissing(['assignment.unit.course.instructors', 'assignment.unit.course.instructor']);
        $course = $submission->assignment?->unit?->course;
        if (! $course) {
            return;
        }

        $instructorIds = collect([$course->instructor_id])
            ->merge($course->instructors->pluck('id'))
            ->filter()
            ->unique()
            ->values();

        $instructors = User::query()->whereIn('id', $instructorIds)->get();
        foreach ($instructors as $instructor) {
            $this->sendToSupportedChannels(
                $instructor,
                __('notifications.grading.manual_grading_required_title'),
                __('notifications.grading.manual_grading_required_message', [
                    'assignment' => $submission->assignment?->title ?? __('notifications.assignment'),
                ]),
                [
                    'submission_id' => $submission->id,
                    'assignment_id' => $submission->assignment_id,
                    'student_id' => $submission->user_id,
                ]
            );
        }

        Log::info(__('notifications.grading.manual_grading_required', [
            'id' => $submission->id,
        ]));
    }

    public function notifyGradeRecalculated(Submission $submission, float $oldScore, float $newScore): void
    {
        $submission->loadMissing(['user', 'assignment']);
        if ($submission->user) {
            $this->sendToSupportedChannels(
                $submission->user,
                __('notifications.grading.grade_recalculated_title'),
                __('notifications.grading.grade_recalculated_message', [
                    'assignment' => $submission->assignment?->title ?? __('notifications.assignment'),
                    'old_score' => $oldScore,
                    'new_score' => $newScore,
                ]),
                [
                    'submission_id' => $submission->id,
                    'assignment_id' => $submission->assignment_id,
                    'old_score' => $oldScore,
                    'new_score' => $newScore,
                ]
            );
        }

        Log::info(__('notifications.grading.grade_recalculated', [
            'id' => $submission->id,
            'old_score' => $oldScore,
            'new_score' => $newScore,
        ]));
    }

    private function sendToSupportedChannels(User $user, string $title, string $message, array $data): void
    {
        foreach ([NotificationPreference::CHANNEL_IN_APP, NotificationPreference::CHANNEL_EMAIL] as $channel) {
            $this->notificationService->sendWithPreferences(
                $user,
                'grading',
                $channel,
                $title,
                $message,
                $data
            );
        }
    }
}
