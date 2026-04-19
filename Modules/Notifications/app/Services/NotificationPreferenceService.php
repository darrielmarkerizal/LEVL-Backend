<?php

namespace Modules\Notifications\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Notifications\Contracts\Services\NotificationPreferenceServiceInterface;
use Modules\Notifications\DTOs\UpdateNotificationPreferencesDTO;
use Modules\Notifications\Enums\NotificationType;
use Modules\Notifications\Models\NotificationPreference;

class NotificationPreferenceService implements NotificationPreferenceServiceInterface
{
    
    public function getPreferences(User $user): Collection
    {
        $preferences = NotificationPreference::where('user_id', $user->id)->get();

        
        if ($preferences->isEmpty()) {
            $this->createDefaultPreferences($user);
            $preferences = NotificationPreference::where('user_id', $user->id)->get();
        }

        return $preferences;
    }

    
    public function updatePreferences(User $user, UpdateNotificationPreferencesDTO|array $preferences): bool
    {
        
        if (is_array($preferences)) {
            $preferences = UpdateNotificationPreferencesDTO::from(['preferences' => $preferences]);
        }

        return DB::transaction(function () use ($user, $preferences) {
            foreach ($preferences->preferences as $preference) {
                NotificationPreference::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'category' => $preference->category,
                        'channel' => $preference->channel,
                    ],
                    [
                        'enabled' => $preference->enabled,
                        'frequency' => $preference->frequency,
                    ]
                );
            }

            return true;
        });
    }

    
    public function shouldSendNotification(User $user, string $category, string $channel): bool
    {
        
        if ($this->isCriticalNotification($category)) {
            return true;
        }

        $categories = $this->resolveCategoryAliases($category);
        $preference = NotificationPreference::where('user_id', $user->id)
            ->whereIn('category', $categories)
            ->where('channel', $channel)
            ->orderByRaw("CASE WHEN category = ? THEN 0 ELSE 1 END", [$category])
            ->first();

        
        if (! $preference) {
            return $this->shouldSendByDefault($categories[0] ?? $category, $channel);
        }

        return $preference->enabled;
    }

    
    public function resetToDefaults(User $user): bool
    {
        try {
            DB::beginTransaction();

            
            NotificationPreference::where('user_id', $user->id)->delete();

            
            $this->createDefaultPreferences($user);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            return false;
        }
    }

    
    public function getDefaultPreferences(): array
    {
        $categories = NotificationPreference::getCategories();
        $channels = NotificationPreference::getChannels();

        return collect($categories)
            ->crossJoin($channels)
            ->map(fn ($item) => [
                'category' => $item[0],
                'channel' => $item[1],
                'enabled' => $this->getDefaultEnabledState($item[0], $item[1]),
                'frequency' => $this->getDefaultFrequency($item[0], $item[1]),
            ])
            ->all();
    }

    
    protected function createDefaultPreferences(User $user): void
    {
        $defaults = $this->getDefaultPreferences();

        foreach ($defaults as $default) {
            NotificationPreference::create([
                'user_id' => $user->id,
                'category' => $default['category'],
                'channel' => $default['channel'],
                'enabled' => $default['enabled'],
                'frequency' => $default['frequency'],
            ]);
        }
    }

    
    protected function isCriticalNotification(string $category): bool
    {
        $criticalCategories = [
            NotificationPreference::CATEGORY_SYSTEM,
        ];

        return collect($criticalCategories)->contains($category);
    }

    
    protected function getDefaultEnabledState(string $category, string $channel): bool
    {
        
        if ($channel === NotificationPreference::CHANNEL_EMAIL) {
            return collect([
                NotificationType::Assignment->value,
                NotificationType::Grading->value,
                NotificationType::Enrollment->value,
                NotificationPreference::CATEGORY_SYSTEM,
            ])->contains($category);
        }

        
        if ($channel === NotificationPreference::CHANNEL_IN_APP) {
            return true;
        }

        if ($channel === NotificationPreference::CHANNEL_PUSH) {
            return false;
        }

        return true;
    }

    
    protected function getDefaultFrequency(string $category, string $channel): string
    {
        
        if ($this->isCriticalNotification($category)) {
            return NotificationPreference::FREQUENCY_IMMEDIATE;
        }

        
        if ($category === NotificationType::Forum->value) {
            return NotificationPreference::FREQUENCY_DAILY;
        }

        return NotificationPreference::FREQUENCY_IMMEDIATE;
    }

    
    protected function shouldSendByDefault(string $category, string $channel): bool
    {
        return $this->getDefaultEnabledState($category, $channel);
    }

    protected function resolveCategoryAliases(string $category): array
    {
        $aliases = match ($category) {
            NotificationType::Assignment->value => [NotificationType::Assignment->value, NotificationType::Assignments->value],
            NotificationType::Assignments->value => [NotificationType::Assignments->value, NotificationType::Assignment->value],
            NotificationType::Gamification->value => [NotificationType::Gamification->value, NotificationType::Achievements->value],
            NotificationType::Achievements->value => [NotificationType::Achievements->value, NotificationType::Gamification->value],
            NotificationType::Forum->value => [
                NotificationType::Forum->value,
                NotificationType::ForumReplyToThread->value,
                NotificationType::ForumReplyToReply->value,
                NotificationType::ForumReactionThread->value,
                NotificationType::ForumReactionReply->value,
            ],
            NotificationType::ForumReplyToThread->value,
            NotificationType::ForumReplyToReply->value,
            NotificationType::ForumReactionThread->value,
            NotificationType::ForumReactionReply->value => [
                $category,
                NotificationType::Forum->value,
            ],
            default => [$category],
        };

        return array_values(array_unique($aliases));
    }
}
