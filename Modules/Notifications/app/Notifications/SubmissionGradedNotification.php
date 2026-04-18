<?php

declare(strict_types=1);

namespace Modules\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Learning\Models\Submission;

class SubmissionGradedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Submission $submission,
        public readonly float $score,
        public readonly ?string $feedback = null
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $assignmentTitle = $this->submission->assignment?->title ?? __('notifications.assignment');

        $message = (new MailMessage)
            ->subject(__('notifications.mail.submission_graded.subject', ['assignment' => $assignmentTitle]))
            ->greeting(__('notifications.mail.submission_graded.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.mail.submission_graded.line_graded', ['assignment' => $assignmentTitle]))
            ->line(__('notifications.mail.submission_graded.score_line', ['score' => $this->score]));

        if ($this->feedback) {
            $message->line(__('notifications.mail.submission_graded.feedback_line', ['feedback' => $this->feedback]));
        }

        return $message
            ->action(__('notifications.mail.submission_graded.action'), $this->getSubmissionUrl())
            ->line(__('notifications.mail.submission_graded.outro'));
    }

    public function toArray($notifiable): array
    {
        $assignmentTitle = $this->submission->assignment?->title ?? __('notifications.assignment');

        return [
            'type' => 'submission_graded',
            'submission_id' => $this->submission->id,
            'assignment_id' => $this->submission->assignment_id,
            'assignment_title' => $this->submission->assignment?->title,
            'score' => $this->score,
            'feedback' => $this->feedback,
            'message' => __('notifications.mail.submission_graded.database_message', [
                'assignment' => $assignmentTitle,
                'score' => $this->score,
            ]),
        ];
    }

    protected function getSubmissionUrl(): string
    {
        return config('app.url')."/submissions/{$this->submission->id}";
    }
}
