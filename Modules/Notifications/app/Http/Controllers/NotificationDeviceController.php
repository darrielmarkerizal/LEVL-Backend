<?php

declare(strict_types=1);

namespace Modules\Notifications\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationDeviceController extends Controller
{
    use ApiResponse;

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'fcm_token' => ['required', 'string', 'max:4096'],
        ]);

        $user = auth('api')->user();
        $user->update([
            'fcm_token' => $payload['fcm_token'],
        ]);

        return $this->success(
            ['fcm_token_registered' => true],
            'messages.updated'
        );
    }

    public function destroy(): JsonResponse
    {
        $user = auth('api')->user();
        $user->update([
            'fcm_token' => null,
        ]);

        return $this->success(
            ['fcm_token_registered' => false],
            'messages.updated'
        );
    }
}
