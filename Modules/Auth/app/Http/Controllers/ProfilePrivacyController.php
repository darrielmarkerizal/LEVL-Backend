<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Http\Requests\UpdatePrivacySettingsRequest;
use Modules\Auth\Services\ProfilePrivacyService;

class ProfilePrivacyController extends Controller
{
  use ApiResponse;

  public function __construct(private ProfilePrivacyService $privacyService) {}

  public function index(Request $request): JsonResponse
  {
    $user = $request->user();
    $settings = $this->privacyService->getPrivacySettings($user);

    return $this->success(new \Modules\Auth\Http\Resources\ProfilePrivacyResource($settings));
  }

  public function update(UpdatePrivacySettingsRequest $request): JsonResponse
  {
    $user = $request->user();
    $settings = $this->privacyService->updatePrivacySettings($user, $request->validated());

    return $this->success(
      new \Modules\Auth\Http\Resources\ProfilePrivacyResource($settings),
      __("messages.profile.privacy_updated")
    );
  }
}
