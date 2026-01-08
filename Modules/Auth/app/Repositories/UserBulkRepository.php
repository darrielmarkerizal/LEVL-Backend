<?php

declare(strict_types=1);


namespace Modules\Auth\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Collection;
use Modules\Auth\Contracts\Repositories\UserBulkRepositoryInterface;
use Modules\Auth\Enums\UserStatus;
use Modules\Auth\Models\User;

class UserBulkRepository extends BaseRepository implements UserBulkRepositoryInterface
{
    protected function model(): string
    {
        return User::class;
    }

    public function findByIds(array $userIds): Collection
    {
        return $this->model()::with('roles')->whereIn('id', $userIds)->get();
    }

    public function findById(int $userId): ?User
    {
        return $this->model()::find($userId);
    }

    public function bulkUpdateStatus(array $userIds, string $status): int
    {
        return $this->model()::whereIn('id', $userIds)->update(['status' => UserStatus::from($status)]);
    }

    public function bulkDelete(array $userIds): int
    {
        return $this->model()::whereIn('id', $userIds)->delete();
    }
}
