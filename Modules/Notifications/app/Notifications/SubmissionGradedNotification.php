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
        $assignmentTitle = $this->submission->assignment?->title ?? 'Assignment';

        return (new MailMessage)
            ->subject("Your submission for \"{$assignmentTitle}\" has been graded")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your submission for \"{$assignmentTitle}\" has been graded.")
            ->line("Your score: {$this->score}/100")
            ->when($this->feedback, function (MailMessage $message) {
                return $message->line("Feedback: {$this->feedback}");
            })
            ->action('View Submission', $this->getSubmissionUrl())
            ->line('Thank you for your hard work!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'submission_graded',
            'submission_id' => $this->submission->id,
            'assignment_id' => $this->submission->assignment_id,
            'assignment_title' => $this->submission->assignment?->title,
            'score' => $this->score,
            'feedback' => $this->feedback,
            'message' => "Your submission for \"{$this->submission->assignment?->title}\" has been graded. Score: {$this->score}/100",
        ];
    }

    protected function getSubmissionUrl(): string
    {
        return config('app.url')."/submissions/{$this->submission->id}";
    }
}
