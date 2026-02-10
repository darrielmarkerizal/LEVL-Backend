<?php

declare(strict_types=1);

namespace Modules\Forums\Listeners;

use Modules\Forums\Events\ReplyCreated;
use Modules\Notifications\Services\NotificationService;

class NotifyAuthorOnReply
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

     
    public function handle(ReplyCreated $event): void
    {
        $reply = $event->reply;
        $thread = $reply->thread;

        
        if ($reply->parent_id) {
            
            $parentReply = $reply->parent;
            if ($parentReply && $parentReply->author_id != $reply->author_id) {
                $this->notificationService->send(
                    $parentReply->author_id,
                    'forum_reply_to_reply',
                    'New Reply to Your Comment',
                    "{$reply->author->name} replied to your comment",
                    [
                        'reply_id' => $reply->id,
                        'thread_id' => $thread->id,
                        'thread_title' => $thread->title,
                        'author_name' => $reply->author->name,
                    ]
                );
            }
        } else {
            
            if ($thread->author_id != $reply->author_id) {
                $this->notificationService->send(
                    $thread->author_id,
                    'forum_reply_to_thread',
                    'New Reply to Your Thread',
                    "{$reply->author->name} replied to your thread",
                    [
                        'reply_id' => $reply->id,
                        'thread_id' => $thread->id,
                        'thread_title' => $thread->title,
                        'author_name' => $reply->author->name,
                    ]
                );
            }
        }
    }
}
