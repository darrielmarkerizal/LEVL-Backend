<?php

declare(strict_types=1);

namespace Modules\Auth\Contracts\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Modules\Auth\Models\User;

interface UserManagementServiceInterface
{
    public function listUsers(User $authUser, int $perPage = 15, ?string $search = null): LengthAwarePaginator;

    public function listUsersForIndex(User $authUser, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function showUser(User $authUser, int $userId, ?Request $request = null): User;

    public function listUserEnrolledCourses(User $authUser, int $userId, ?Request $request = null, int $perPage = 15): LengthAwarePaginator;

    public function listInstructorAssignedSchemes(User $authUser, int $userId, ?Request $request = null, int $perPage = 15): LengthAwarePaginator;

    public function listUserLatestActivities(User $authUser, int $userId, ?Request $request = null, int $perPage = 15): LengthAwarePaginator;

    public function updateUser(User $authUser, int $userId, array $data): User;

    public function updateUserStatus(User $authUser, int $userId, string $status): User;

    public function resetPassword(User $authUser, int $userId, string $newPassword): User;

    public function deleteUser(User $authUser, int $userId): void;

    public function createUser(User $authUser, array $validated): User;

    public function updateProfile(User $user, array $validated, ?string $ip, ?string $userAgent): User;

    public function searchGlobal(string $query, int $limit = 5): \Illuminate\Support\Collection;
}
