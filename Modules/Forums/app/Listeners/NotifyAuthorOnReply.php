<?php

declare(strict_types=1);

namespace Modules\Forums\Listeners;

use Modules\Forums\Events\ReplyCreated;
use Modules\Notifications\Enums\NotificationType;
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
                $parentAuthor = $parentReply->author;
                if (! $parentAuthor) {
                    return;
                }

                $this->notificationService->notifyByPreferences(
                    $parentAuthor,
                    NotificationType::ForumReplyToReply->value,
                    __('notifications.forum.reply_to_comment_title'),
                    __('notifications.forum.reply_to_comment_message', [
                        'author' => $reply->author->name,
                    ]),
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
                $threadAuthor = $thread->author;
                if (! $threadAuthor) {
                    return;
                }

                $this->notificationService->notifyByPreferences(
                    $threadAuthor,
                    NotificationType::ForumReplyToThread->value,
                    __('notifications.forum.reply_to_thread_title'),
                    __('notifications.forum.reply_to_thread_message', [
                        'author' => $reply->author->name,
                    ]),
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
