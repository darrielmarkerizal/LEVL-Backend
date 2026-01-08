<?php

declare(strict_types=1);

namespace Modules\Auth\Tests\Unit\Services;

use Modules\Auth\Contracts\Repositories\UserBulkRepositoryInterface;
use Modules\Auth\Enums\UserStatus;
use Modules\Auth\Jobs\ExportUsersToEmailJob;
use Modules\Auth\Models\User;
use Modules\Auth\Services\UserBulkService;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Mockery;

class UserBulkServiceTest extends TestCase
{
    private UserBulkService $service;
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = Mockery::mock(UserBulkRepositoryInterface::class);
        $this->service = new UserBulkService($this->repository);
    }

    public function test_export_dispatches_job_with_provided_ids()
    {
        Queue::fake();

        $authUser = Mockery::mock(User::class);
        $authUser->email = 'admin@example.com';
        
        $data = [
            'user_ids' => [1, 2, 3],
            'email' => 'recipient@example.com'
        ];

        $this->service->export($authUser, $data);

        Queue::assertPushed(ExportUsersToEmailJob::class, function ($job) {
            return $job->userIds === [1, 2, 3] && $job->recipientEmail === 'recipient@example.com';
        });
    }

    public function test_bulk_activate_updates_status_and_returns_count()
    {
        $userIds = [1, 2];
        $changedBy = 99;

        $this->repository->shouldReceive('bulkUpdateStatus')
            ->once()
            ->with($userIds, UserStatus::Active->value)
            ->andReturn(2);

        // Mock users for logging
        $user1 = Mockery::mock(User::class)->makePartial();
        $user1->id = 1;
        $user1->status = UserStatus::Inactive;
        $user1->shouldReceive('logActivity')->once();

        $user2 = Mockery::mock(User::class)->makePartial();
        $user2->id = 2;
        $user2->status = UserStatus::Inactive;
        $user2->shouldReceive('logActivity')->once();

        $this->repository->shouldReceive('findById')->with(1)->andReturn($user1);
        $this->repository->shouldReceive('findById')->with(2)->andReturn($user2);

        $count = $this->service->bulkActivate($userIds, $changedBy);

        $this->assertEquals(2, $count);
    }
}
