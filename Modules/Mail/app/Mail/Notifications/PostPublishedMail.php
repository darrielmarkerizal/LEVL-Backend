<?php

declare(strict_types=1);

namespace Modules\Mail\Mail\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Auth\Models\User;
use Modules\Notifications\app\Models\Post;

class PostPublishedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly Post $post,
        public readonly string $postUrl
    ) {
        $this->onQueue('emails-transactional');
    }

    public function build(): self
    {
        return $this->subject(__('mail.post_published.subject'))
            ->view('mail::emails.notifications.post-published')
            ->with([
                'user' => $this->user,
                'post' => $this->post,
                'postUrl' => $this->postUrl,
            ]);
    }
}
