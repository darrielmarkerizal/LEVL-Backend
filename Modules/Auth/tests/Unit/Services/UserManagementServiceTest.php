<?php

declare(strict_types=1);

namespace Modules\Auth\Tests\Unit\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Modules\Auth\Contracts\Repositories\AuthRepositoryInterface;
use Modules\Auth\Contracts\UserAccessPolicyInterface;
use Modules\Auth\Models\User;
use Modules\Auth\Services\UserManagementService;
use Tests\TestCase;

class UserManagementServiceTest extends TestCase
{
    private UserManagementService $service;
    private $authRepository;
    private $userAccessPolicy;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->authRepository = $this->mock(AuthRepositoryInterface::class);
        $this->userAccessPolicy = $this->mock(UserAccessPolicyInterface::class);
        
        $this->service = new UserManagementService($this->authRepository, $this->userAccessPolicy);
    }

    public function test_show_user_unauthorized_if_student()
    {
        $this->expectException(AuthorizationException::class);

        $authUser = \Mockery::mock(User::class)->makePartial();
        $authUser->shouldReceive('hasRole')->with('Superadmin')->andReturn(false);
        $authUser->shouldReceive('hasRole')->with('Admin')->andReturn(false);
        
        $targetUser = new User();

        $this->service->showUser($authUser, $targetUser);
    }

    public function test_show_user_admin_with_policy_access()
    {
        $authUser = \Mockery::mock(User::class)->makePartial();
        $authUser->shouldReceive('hasRole')->with('Superadmin')->andReturn(false);
        $authUser->shouldReceive('hasRole')->with('Admin')->andReturn(true);
        
        $targetUser = new User();
        $targetUser->id = 55;

        $this->userAccessPolicy->shouldReceive('canAdminViewUser')
            ->with($authUser, $targetUser)
            ->once()
            ->andReturn(true);

        $result = $this->service->showUser($authUser, $targetUser);

        $this->assertEquals($targetUser->id, $result->id);
    }
}
