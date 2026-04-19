<?php

namespace Modules\Notifications\Contracts\Services;

use Illuminate\Support\Collection;
use Modules\Auth\Models\User;
use Modules\Notifications\DTOs\UpdateNotificationPreferencesDTO;

interface NotificationPreferenceServiceInterface
{
    
    public function getPreferences(User $user): Collection;

    
    public function updatePreferences(User $user, UpdateNotificationPreferencesDTO|array $preferences): bool;

    
    public function shouldSendNotification(User $user, string $category, string $channel): bool;

    
    public function resetToDefaults(User $user): bool;

    
    public function getDefaultPreferences(): array;
}
