<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers;

use App\Contracts\Services\ProfileServiceInterface;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Models\User;

class PublicProfileController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ProfileServiceInterface $profileService
    ) {}

    public function show(Request $request, int $userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $viewer = $request->user();
        $profileData = $this->profileService->getPublicProfile($user, $viewer);

        return $this->success(new \Modules\Auth\Http\Resources\ProfileResource($profileData));
    }
}
