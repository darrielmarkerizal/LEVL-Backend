<?php

declare(strict_types=1);

namespace Modules\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Learning\Models\Submission;

class GradeRecalculatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Submission $submission,
        public readonly float $oldScore,
        public readonly float $newScore
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $assignmentTitle = $this->submission->assignment?->title ?? __('notifications.assignment');
        $scoreChange = $this->newScore - $this->oldScore;
        $changeAmount = abs($scoreChange);
        $changeLine = match (true) {
            $scoreChange > 0 => __('notifications.mail.grade_recalculated.change_increased', ['amount' => $changeAmount]),
            $scoreChange < 0 => __('notifications.mail.grade_recalculated.change_decreased', ['amount' => $changeAmount]),
            default => __('notifications.mail.grade_recalculated.change_unchanged'),
        };

        return (new MailMessage)
            ->subject(__('notifications.mail.grade_recalculated.subject', ['assignment' => $assignmentTitle]))
            ->greeting(__('notifications.mail.grade_recalculated.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.mail.grade_recalculated.line_recalculated', ['assignment' => $assignmentTitle]))
            ->line(__('notifications.mail.grade_recalculated.previous_score', ['score' => $this->oldScore]))
            ->line(__('notifications.mail.grade_recalculated.new_score', ['score' => $this->newScore]))
            ->line($changeLine)
            ->action(__('notifications.mail.grade_recalculated.action'), $this->getSubmissionUrl())
            ->line(__('notifications.mail.grade_recalculated.outro'));
    }

    public function toArray($notifiable): array
    {
        $assignmentTitle = $this->submission->assignment?->title ?? __('notifications.assignment');

        return [
            'type' => 'grade_recalculated',
            'submission_id' => $this->submission->id,
            'assignment_id' => $this->submission->assignment_id,
            'assignment_title' => $this->submission->assignment?->title,
            'old_score' => $this->oldScore,
            'new_score' => $this->newScore,
            'score_change' => $this->newScore - $this->oldScore,
            'message' => __('notifications.mail.grade_recalculated.database_message', [
                'assignment' => $assignmentTitle,
                'old_score' => $this->oldScore,
                'new_score' => $this->newScore,
            ]),
        ];
    }

    protected function getSubmissionUrl(): string
    {
        return config('app.url')."/submissions/{$this->submission->id}";
    }
}
