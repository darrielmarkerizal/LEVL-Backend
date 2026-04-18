<?php

declare(strict_types=1);

namespace Modules\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Submission;

class GradesReleasedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Assignment $assignment,
        public readonly ?Submission $submission = null,
        public readonly ?float $score = null
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $assignmentTitle = $this->assignment->title ?? __('notifications.assignment');

        $message = (new MailMessage)
            ->subject(__('notifications.mail.grades_released.subject', ['assignment' => $assignmentTitle]))
            ->greeting(__('notifications.mail.grades_released.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.mail.grades_released.line_released', ['assignment' => $assignmentTitle]));

        if ($this->score !== null) {
            $message->line(__('notifications.mail.grades_released.score_line', ['score' => $this->score]));
        }

        return $message
            ->line(__('notifications.mail.grades_released.line_view_feedback'))
            ->action(__('notifications.mail.grades_released.action'), $this->getResultsUrl())
            ->line(__('notifications.mail.grades_released.outro'));
    }

    public function toArray($notifiable): array
    {
        $assignmentTitle = $this->assignment->title ?? __('notifications.assignment');

        return [
            'type' => 'grades_released',
            'assignment_id' => $this->assignment->id,
            'assignment_title' => $this->assignment->title,
            'submission_id' => $this->submission?->id,
            'score' => $this->score,
            'message' => __('notifications.mail.grades_released.database_message', ['assignment' => $assignmentTitle]),
        ];
    }

    protected function getResultsUrl(): string
    {
        if ($this->submission) {
            return config('app.url')."/submissions/{$this->submission->id}";
        }

        return config('app.url')."/assignments/{$this->assignment->id}";
    }
}
