<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Contracts\Services\UserManagementServiceInterface;
use Modules\Auth\Http\Requests\CreateUserRequest;
use Modules\Auth\Http\Requests\UpdateUserStatusRequest;
use Modules\Auth\Http\Requests\UserIncludeRequest;
use Modules\Auth\Http\Resources\UserResource;

class UserManagementController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly UserManagementServiceInterface $userManagementService
    ) {}

    public function index(UserIncludeRequest $request): JsonResponse
    {
        $users = $this->userManagementService->listUsersForIndex(
            $request->user(),
            $request->except('include'),
            (int) $request->query('per_page', 15)
        );

        $includes = $request->getIncludes();

        if (! empty($includes)) {
            $users->load($includes);
        }

        $users->getCollection()->transform(fn ($user) => new \Modules\Auth\Http\Resources\UserIndexResource($user));

        return $this->paginateResponse($users);
    }

    public function show(UserIncludeRequest $request, int $id): JsonResponse
    {
        $user = $this->userManagementService->showUser(auth()->user(), $id, $request);

        $includes = $request->getIncludes();

        if (! empty($includes)) {
            $user->load($includes);
        }

        return $this->success(new UserResource($user), 'messages.data_retrieved');
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = $this->userManagementService->createUser(
            $request->user(),
            $request->validated()
        );

        return $this->created(new UserResource($user), 'messages.auth.user_created_success');
    }

    public function update(UpdateUserStatusRequest $request, int $id): JsonResponse
    {
        $user = $this->userManagementService->updateUserStatus(
            auth()->user(),
            $id,
            $request->input('status')
        );

        return $this->success(new UserResource($user), 'messages.auth.status_updated');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->userManagementService->deleteUser(auth()->user(), $id);

        return $this->success(null, 'messages.deleted');
    }
}
