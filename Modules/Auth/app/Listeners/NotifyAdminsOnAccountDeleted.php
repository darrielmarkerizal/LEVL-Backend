<?php

declare(strict_types=1);

namespace Modules\Auth\Listeners;

use Modules\Auth\Enums\UserStatus;
use Modules\Auth\Events\AccountDeleted;
use Modules\Auth\Models\User;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Services\NotificationService;

class NotifyAdminsOnAccountDeleted
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function handle(AccountDeleted $event): void
    {
        $admins = User::query()
            ->where('status', UserStatus::Active->value)
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['Admin', 'Superadmin']))
            ->get();

        foreach ($admins as $admin) {
            $this->notificationService->notifyByPreferences(
                $admin,
                NotificationType::System->value,
                'Akun pengguna dihapus',
                "{$event->user->name} ({$event->user->email}) telah menghapus akun.",
                [
                    'deleted_user_id' => $event->user->id,
                ],
                channels: ['in_app']
            );
        }
    }
}
