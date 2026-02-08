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
        $assignmentTitle = $this->submission->assignment?->title ?? 'Assignment';
        $scoreChange = $this->newScore - $this->oldScore;
        $changeDirection = $scoreChange > 0 ? 'increased' : 'decreased';
        $changeAmount = abs($scoreChange);

        return (new MailMessage)
            ->subject("Your grade for \"{$assignmentTitle}\" has been updated")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your grade for \"{$assignmentTitle}\" has been recalculated due to answer key updates.")
            ->line("Previous score: {$this->oldScore}/100")
            ->line("New score: {$this->newScore}/100")
            ->line("Your score has {$changeDirection} by {$changeAmount} points.")
            ->action('View Submission', $this->getSubmissionUrl())
            ->line('If you have any questions about this change, please contact your instructor.');
    }

    public function toArray($notifiable): array
    {
        $scoreChange = $this->newScore - $this->oldScore;

        return [
            'type' => 'grade_recalculated',
            'submission_id' => $this->submission->id,
            'assignment_id' => $this->submission->assignment_id,
            'assignment_title' => $this->submission->assignment?->title,
            'old_score' => $this->oldScore,
            'new_score' => $this->newScore,
            'score_change' => $scoreChange,
            'message' => "Your grade for \"{$this->submission->assignment?->title}\" has been updated from {$this->oldScore} to {$this->newScore}.",
        ];
    }

    protected function getSubmissionUrl(): string
    {
        return config('app.url')."/submissions/{$this->submission->id}";
    }
}
