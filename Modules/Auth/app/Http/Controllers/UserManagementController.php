<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Contracts\Services\UserManagementServiceInterface;
use Modules\Auth\Http\Requests\AdminResetPasswordRequest;
use Modules\Auth\Http\Requests\CreateUserRequest;
use Modules\Auth\Http\Requests\UpdateUserRequest;
use Modules\Auth\Http\Resources\InstructorAssignedSchemeResource;
use Modules\Auth\Http\Resources\UserEnrolledCourseResource;
use Modules\Auth\Http\Resources\UserLatestActivityResource;
use Modules\Auth\Http\Resources\UserResource;

class UserManagementController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly UserManagementServiceInterface $userManagementService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $users = $this->userManagementService->listUsersForIndex(
            $request->user(),
            $request->all(),
            (int) $request->query('per_page', 15)
        );

        $users->getCollection()->transform(fn ($user) => new \Modules\Auth\Http\Resources\UserIndexResource($user));

        return $this->paginateResponse($users);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $this->userManagementService->showUser(auth()->user(), $id, $request);

        return $this->success(new UserResource($user), 'messages.data_retrieved');
    }

    public function enrolledCourse(Request $request, int $id): JsonResponse
    {
        $enrolledCourses = $this->userManagementService->listUserEnrolledCourses(
            $request->user(),
            $id,
            $request,
            (int) $request->query('per_page', 15)
        );

        $enrolledCourses->getCollection()->transform(
            fn ($enrollment) => new UserEnrolledCourseResource($enrollment)
        );

        return $this->paginateResponse($enrolledCourses, 'messages.data_retrieved');
    }

    public function assignedSchemes(Request $request, int $id): JsonResponse
    {
        $assignedSchemes = $this->userManagementService->listInstructorAssignedSchemes(
            $request->user(),
            $id,
            $request,
            (int) $request->query('per_page', 15)
        );

        $assignedSchemes->getCollection()->transform(
            fn ($course) => new InstructorAssignedSchemeResource($course)
        );

        return $this->paginateResponse($assignedSchemes, 'messages.data_retrieved');
    }

    public function latestActivity(Request $request, int $id): JsonResponse
    {
        $activities = $this->userManagementService->listUserLatestActivities(
            $request->user(),
            $id,
            $request,
            (int) $request->query('per_page', 15)
        );

        $activities->getCollection()->transform(
            fn ($activity) => new UserLatestActivityResource($activity)
        );

        return $this->paginateResponse($activities, 'messages.data_retrieved');
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = $this->userManagementService->createUser(
            $request->user(),
            $request->validated()
        );

        return $this->created(new UserResource($user), 'messages.auth.user_created_success');
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();
        $data = [];

        if (isset($validated['username'])) {
            $data['username'] = $validated['username'];
        }
        if (isset($validated['status'])) {
            $data['status'] = $validated['status'];
        }
        if (isset($validated['role'])) {
            $data['role'] = $validated['role'];
        }
        if (isset($validated['password'])) {
            $data['password'] = $validated['password'];
        }
        if (array_key_exists('specialization_id', $validated)) {
            $data['specialization_id'] = $validated['specialization_id'];
        }

        $user = $this->userManagementService->updateUser(
            auth()->user(),
            $id,
            $data
        );

        return $this->success(new UserResource($user), 'messages.auth.user_updated');
    }

    public function resetPassword(AdminResetPasswordRequest $request, int $id): JsonResponse
    {
        $user = $this->userManagementService->resetPassword(
            auth()->user(),
            $id,
            $request->input('password')
        );

        return $this->success(new UserResource($user), 'messages.auth.password_reset_success');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->userManagementService->deleteUser(auth()->user(), $id);

        return $this->success(null, 'messages.deleted');
    }
}
