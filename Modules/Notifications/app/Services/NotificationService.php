<?php

namespace Modules\Notifications\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Auth\Models\User;
use Modules\Notifications\Contracts\Services\NotificationPreferenceServiceInterface;
use Modules\Notifications\Models\Notification;
use Modules\Notifications\Models\NotificationPreference;
use Modules\Notifications\Models\UserNotification;

class NotificationService
{
    protected NotificationPreferenceServiceInterface $preferenceService;
    protected FirebasePushService $firebasePushService;

    public function __construct(
        NotificationPreferenceServiceInterface $preferenceService,
        FirebasePushService $firebasePushService
    )
    {
        $this->preferenceService = $preferenceService;
        $this->firebasePushService = $firebasePushService;
    }

    
    public function create(CreateNotificationDTO|array $data): Notification
    {
        
        if (is_array($data)) {
            $data = CreateNotificationDTO::from($data);
        }

        $userId = $data->userId;
        $notificationData = $data->toModelArray();

        $notification = Notification::create($notificationData);

        if ($userId) {
            $notification->users()->attach($userId);
        }

        return $notification;
    }

    public function listForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Notification::query()
            ->whereHas('users', fn ($query) => $query->where('users.id', $userId))
            ->with(['users' => fn ($query) => $query->where('users.id', $userId)])
            ->latest('notifications.created_at')
            ->paginate($perPage);
    }

    public function findForUser(int $userId, int $notificationId): ?Notification
    {
        return Notification::query()
            ->whereKey($notificationId)
            ->whereHas('users', fn ($query) => $query->where('users.id', $userId))
            ->with(['users' => fn ($query) => $query->where('users.id', $userId)])
            ->first();
    }

    public function markAsRead(Notification $notification, ?int $userId = null): bool
    {
        $query = UserNotification::query()->where('notification_id', $notification->id);
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->update([
            'status' => 'read',
            'read_at' => now(),
            'updated_at' => now(),
        ]) > 0;
    }

    public function markAllAsRead(int $userId): int
    {
        return UserNotification::query()
            ->where('user_id', $userId)
            ->where('status', 'unread')
            ->update([
                'status' => 'read',
                'read_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function unreadCount(int $userId): int
    {
        return UserNotification::query()
            ->where('user_id', $userId)
            ->where('status', 'unread')
            ->count();
    }

    public function deleteForUser(Notification $notification, int $userId): bool
    {
        $detached = $notification->users()->detach($userId);
        if ($detached === 0) {
            return false;
        }

        if (! $notification->users()->exists()) {
            $notification->delete();
        }

        return true;
    }

    public function toPayload(Notification $notification, int $userId): array
    {
        $pivot = $notification->users->first()?->pivot;
        $readAt = $pivot?->read_at;

        if (is_string($readAt)) {
            $readAt = Carbon::parse($readAt);
        }

        return [
            'id' => $notification->id,
            'type' => $notification->type?->value ?? $notification->type,
            'title' => $notification->title,
            'message' => $notification->message,
            'data' => $notification->data,
            'action_url' => $notification->action_url,
            'channel' => $notification->channel?->value ?? $notification->channel,
            'priority' => $notification->priority?->value ?? $notification->priority,
            'is_read' => $pivot?->status === 'read',
            'read_at' => $readAt?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
            'updated_at' => $notification->updated_at?->toIso8601String(),
            'user_id' => $userId,
        ];
    }

    
    public function send(SendNotificationDTO|int $userIdOrDto, ?string $type = null, ?string $title = null, ?string $message = null, ?array $data = null): Notification
    {
        
        if ($userIdOrDto instanceof SendNotificationDTO) {
            $dto = $userIdOrDto;

            return $this->create([
                'user_id' => $dto->userId,
                'type' => $dto->type,
                'title' => $dto->title,
                'message' => $dto->message,
                'data' => $dto->data,
            ]);
        }

        
        return $this->create([
            'user_id' => $userIdOrDto,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    
    public function sendWithPreferences(
        User $user,
        string $category,
        string $channel,
        string $title,
        string $message,
        ?array $data = null,
        bool $isCritical = false
    ): ?Notification {
        
        if ($isCritical) {
            return $this->sendToChannel($user, $channel, $category, $title, $message, $data);
        }

        
        if (! $this->preferenceService->shouldSendNotification($user, $category, $channel)) {
            return null;
        }

        return $this->sendToChannel($user, $channel, $category, $title, $message, $data);
    }

    public function notifyByPreferences(
        User $user,
        string $category,
        string $title,
        string $message,
        ?array $data = null,
        ?array $channels = null,
        bool $isCritical = false
    ): void {
        $targetChannels = $channels ?? $this->getEnabledPreferenceChannels();
        foreach ($targetChannels as $channel) {
            $this->sendWithPreferences(
                $user,
                $category,
                $channel,
                $title,
                $message,
                $data,
                $isCritical
            );
        }
    }

    public function getEnabledPreferenceChannels(): array
    {
        return NotificationPreference::getChannels();
    }

    
    protected function sendToChannel(
        User $user,
        string $channel,
        string $category,
        string $title,
        string $message,
        ?array $data = null
    ): Notification {
        $notificationData = [
            'user_id' => $user->id,
            'type' => $category,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'channel' => $channel,
        ];

        
        switch ($channel) {
            case NotificationPreference::CHANNEL_EMAIL:
                $this->sendEmailNotification($user, $title, $message, $data);
                break;
            case NotificationPreference::CHANNEL_PUSH:
                $this->sendPushNotification($user, $title, $message, $data);
                break;
            case NotificationPreference::CHANNEL_IN_APP:
            default:
                
                break;
        }

        return $this->create($notificationData);
    }

    
    protected function sendEmailNotification(User $user, string $title, string $message, ?array $data = null): void
    {
        if (! $user->email) {
            return;
        }

        $payload = $data ? "\n\n".json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '';

        Mail::raw($message.$payload, function ($mail) use ($user, $title): void {
            $mail->to($user->email)->subject($title);
        });
    }

    
    protected function sendPushNotification(User $user, string $title, string $message, ?array $data = null): void
    {
        $token = (string) ($user->fcm_token ?? '');
        if ($token === '') {
            Log::warning('Push notification skipped because user has no FCM token', [
                'user_id' => $user->id,
                'title' => $title,
            ]);
            return;
        }

        $sent = $this->firebasePushService->sendToToken($token, $title, $message, $data);
        if (! $sent) {
            Log::warning('Push notification failed to send', [
                'user_id' => $user->id,
                'title' => $title,
            ]);
            return;
        }

        Log::info('Push notification sent', [
            'user_id' => $user->id,
            'title' => $title,
        ]);
    }
}
