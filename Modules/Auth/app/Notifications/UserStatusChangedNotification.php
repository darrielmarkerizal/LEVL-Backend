<?php

declare(strict_types=1);

namespace Modules\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Auth\Enums\UserStatus;
use Modules\Auth\Models\User;

/**
 * Notification sent to users when their account status changes.
 */
class UserStatusChangedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public UserStatus $oldStatus,
        public UserStatus $newStatus,
        public ?User $changedBy = null,
        public ?string $reason = null
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject(__('notifications.user_status_changed.subject'))
            ->greeting(__('notifications.user_status_changed.greeting', ['name' => $notifiable->name]));

        // Customize message based on new status
        $message = match ($this->newStatus) {
            UserStatus::Active => $message
                ->line(__('notifications.user_status_changed.activated'))
                ->line(__('notifications.user_status_changed.can_access_all_features'))
                ->action(__('notifications.user_status_changed.login_now'), url('/login')),

            UserStatus::Inactive => $message
                ->line(__('notifications.user_status_changed.deactivated'))
                ->line(__('notifications.user_status_changed.contact_admin_for_reactivation'))
                ->line($this->reason ? __('notifications.user_status_changed.reason', ['reason' => $this->reason]) : ''),

            UserStatus::Banned => $message
                ->line(__('notifications.user_status_changed.banned'))
                ->line(__('notifications.user_status_changed.contact_admin_for_appeal'))
                ->line($this->reason ? __('notifications.user_status_changed.reason', ['reason' => $this->reason]) : ''),

            default => $message->line(__('notifications.user_status_changed.status_updated')),
        };

        return $message->line(__('notifications.user_status_changed.thank_you'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
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
