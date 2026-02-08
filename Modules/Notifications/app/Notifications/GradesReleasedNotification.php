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
        $assignmentTitle = $this->assignment->title ?? 'Assignment';

        $message = (new MailMessage)
            ->subject("Grades released for \"{$assignmentTitle}\"")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Grades have been released for \"{$assignmentTitle}\".");

        if ($this->score !== null) {
            $message->line("Your score: {$this->score}/100");
        }

        return $message
            ->line('You can now view your detailed feedback and answers.')
            ->action('View Results', $this->getResultsUrl())
            ->line('Thank you for your participation!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'grades_released',
            'assignment_id' => $this->assignment->id,
            'assignment_title' => $this->assignment->title,
            'submission_id' => $this->submission?->id,
            'score' => $this->score,
            'message' => "Grades have been released for \"{$this->assignment->title}\". You can now view your detailed feedback.",
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
