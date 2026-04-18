<?php

declare(strict_types=1);

namespace Modules\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Learning\Models\Submission;

class ManualGradingRequiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Submission $submission,
        public readonly int $questionsRequiringGrading = 0
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $assignmentTitle = $this->submission->assignment?->title ?? __('notifications.assignment');
        $studentName = $this->submission->user?->name ?? __('notifications.mail.student_fallback');

        $message = (new MailMessage)
            ->subject(__('notifications.mail.manual_grading_required.subject', ['assignment' => $assignmentTitle]))
            ->greeting(__('notifications.mail.manual_grading_required.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.mail.manual_grading_required.line_body', [
                'student' => $studentName,
                'assignment' => $assignmentTitle,
            ]));

        if ($this->questionsRequiringGrading > 0) {
            $message->line(__('notifications.mail.manual_grading_required.questions_line', [
                'count' => $this->questionsRequiringGrading,
            ]));
        }

        return $message
            ->action(__('notifications.mail.manual_grading_required.action'), $this->getGradingUrl())
            ->line(__('notifications.mail.manual_grading_required.outro'));
    }

    public function toArray($notifiable): array
    {
        $assignmentTitle = $this->submission->assignment?->title ?? __('notifications.assignment');

        return [
            'type' => 'manual_grading_required',
            'submission_id' => $this->submission->id,
            'assignment_id' => $this->submission->assignment_id,
            'assignment_title' => $this->submission->assignment?->title,
            'student_id' => $this->submission->user_id,
            'student_name' => $this->submission->user?->name,
            'questions_requiring_grading' => $this->questionsRequiringGrading,
            'submitted_at' => $this->submission->submitted_at?->toIso8601String(),
            'message' => __('notifications.mail.manual_grading_required.database_message', ['assignment' => $assignmentTitle]),
        ];
    }

    protected function getGradingUrl(): string
    {
        return config('app.url')."/grading/submissions/{$this->submission->id}";
    }
}
