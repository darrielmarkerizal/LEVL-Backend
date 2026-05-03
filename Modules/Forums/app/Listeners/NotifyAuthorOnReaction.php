<?php

namespace Modules\Forums\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Forums\Events\ReactionAdded;
use Modules\Forums\Models\Reply;
use Modules\Forums\Models\Thread;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyAuthorOnReaction implements ShouldQueue
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(ReactionAdded $event): void
    {
        $reaction = $event->reaction;
        $reactable = $reaction->reactable;

        if ($reactable->author_id == $reaction->user_id) {
            return;
        }

        $reactionType = $reaction->type->label();
        $userName = $reaction->user->name;

        if ($reactable instanceof Thread) {
            if (! $reactable->author) {
                return;
            }

            $this->notificationService->notifyByPreferences(
                $reactable->author,
                NotificationType::ForumReactionThread->value,
                __('notifications.forum.reaction_title'),
                __('notifications.forum.reaction_thread_message', [
                    'user' => $userName,
                    'reaction' => $reactionType,
                ]),
                [
                    'thread_id' => $reactable->id,
                    'thread_title' => $reactable->title,
                    'user_name' => $userName,
                    'reaction_type' => $reactionType,
                ]
            );
        } elseif ($reactable instanceof Reply) {
            if (! $reactable->author) {
                return;
            }

            $this->notificationService->notifyByPreferences(
                $reactable->author,
                NotificationType::ForumReactionReply->value,
                __('notifications.forum.reaction_title'),
                __('notifications.forum.reaction_reply_message', [
                    'user' => $userName,
                    'reaction' => $reactionType,
                ]),
                [
                    'reply_id' => $reactable->id,
                    'thread_id' => $reactable->thread_id,
                    'thread_title' => $reactable->thread->title,
                    'user_name' => $userName,
                    'reaction_type' => $reactionType,
                ]
            );
        }
    }
}
