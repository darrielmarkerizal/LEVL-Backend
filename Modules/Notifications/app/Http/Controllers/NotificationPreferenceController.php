<?php

namespace Modules\Notifications\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Notifications\Contracts\Services\NotificationPreferenceServiceInterface;
use Modules\Notifications\Models\NotificationPreference;


class NotificationPreferenceController extends Controller
{
    use ApiResponse;

    protected NotificationPreferenceServiceInterface $preferenceService;

    public function __construct(NotificationPreferenceServiceInterface $preferenceService)
    {
        $this->preferenceService = $preferenceService;
    }

    
    public function index(Request $request): JsonResponse
    {
        $preferences = $this->preferenceService->getPreferences(auth()->user());

        return $this->success(
            data: $preferences,
            meta: [
                'categories' => NotificationPreference::getCategories(),
                'channels' => NotificationPreference::getChannels(),
                'frequencies' => NotificationPreference::getFrequencies(),
            ],
        );
    }

    
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'preferences' => 'required|array',
            'preferences.*.category' => 'required|string|in:'.implode(',', NotificationPreference::getCategories()),
            'preferences.*.channel' => 'required|string|in:'.implode(',', NotificationPreference::getChannels()),
            'preferences.*.enabled' => 'required|boolean',
            'preferences.*.frequency' => 'required|string|in:'.implode(',', NotificationPreference::getFrequencies()),
        ]);

        $success = $this->preferenceService->updatePreferences(
            auth()->user(),
            $validated['preferences'],
        );

        if ($success) {
            $preferences = $this->preferenceService->getPreferences(auth()->user());

            return $this->success(
                data: $preferences,
                message: __('messages.notifications.preferences_updated'),
            );
        }

        return $this->error(
            message: __('messages.notifications.failed_update_preferences'),
            status: 500,
        );
    }

    
    public function reset(Request $request): JsonResponse
    {
        $success = $this->preferenceService->resetToDefaults(auth()->user());

        if ($success) {
            $preferences = $this->preferenceService->getPreferences(auth()->user());

            return $this->success(
                data: $preferences,
                message: __('messages.notifications.preferences_reset'),
            );
        }

        return $this->error(
            message: __('messages.notifications.failed_reset_preferences'),
            status: 500,
        );
    }
}
