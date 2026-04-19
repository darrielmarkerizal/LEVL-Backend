<?php

declare(strict_types=1);

namespace Modules\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Auth\Enums\UserStatus;
use Modules\Auth\Models\User;


class UserStatusChangedNotification extends Notification
{
    use Queueable;

    
    public function __construct(
        public UserStatus $oldStatus,
        public UserStatus $newStatus,
        public ?User $changedBy = null,
        public ?string $reason = null
    ) {}

    
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject(__('auth::notifications.user_status_changed.subject'))
            ->greeting(__('auth::notifications.user_status_changed.greeting', ['name' => $notifiable->name]));

        
        $message = match ($this->newStatus) {
            UserStatus::Active => $message
                ->line(__('auth::notifications.user_status_changed.activated'))
                ->line(__('auth::notifications.user_status_changed.can_access_all_features'))
                ->action(__('auth::notifications.user_status_changed.login_now'), url('/login')),

            UserStatus::Inactive => $message
                ->line(__('auth::notifications.user_status_changed.deactivated'))
                ->line(__('auth::notifications.user_status_changed.contact_admin_for_reactivation'))
                ->line($this->reason ? __('auth::notifications.user_status_changed.reason', ['reason' => $this->reason]) : ''),

            UserStatus::Banned => $message
                ->line(__('auth::notifications.user_status_changed.banned'))
                ->line(__('auth::notifications.user_status_changed.contact_admin_for_appeal'))
                ->line($this->reason ? __('auth::notifications.user_status_changed.reason', ['reason' => $this->reason]) : ''),

            default => $message->line(__('auth::notifications.user_status_changed.status_updated')),
        };

        return $message->line(__('auth::notifications.user_status_changed.thank_you'));
    }

    
    public function toArray(object $notifiable): array
    {
        return [
            'old_status' => $this->oldStatus->value,
            'new_status' => $this->newStatus->value,
            'changed_by_id' => $this->changedBy?->id,
            'changed_by_name' => $this->changedBy?->name,
            'reason' => $this->reason,
        ];
    }
}
