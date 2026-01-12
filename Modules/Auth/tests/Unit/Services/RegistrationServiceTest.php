<?php

declare(strict_types=1);

namespace Modules\Auth\Tests\Unit\Services;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Contracts\Repositories\AuthRepositoryInterface;
use Modules\Auth\DTOs\RegisterDTO;
use Modules\Auth\Events\UserRegistered;
use Modules\Auth\Models\User;
use Modules\Auth\Services\AuthenticationService;
use Modules\Auth\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationServiceTest extends TestCase
{
    use RefreshDatabase;
    private RegistrationService $service;
    private $authRepository;
    private $authenticationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->authRepository = $this->mock(AuthRepositoryInterface::class);
        $this->authenticationService = $this->mock(AuthenticationService::class);
        
        $this->service = new RegistrationService($this->authRepository, $this->authenticationService);

        Role::create(['name' => 'Student', 'guard_name' => 'api']);
    }

    public function test_register_student_successfully()
    {
        Event::fake();

        $dto = new RegisterDTO(
            name: 'Test Student',
            email: 'student@test.com',
            username: 'student1',
            password: 'password123'
        );

        // Create mock user first
        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('assignRole')->with('Student')->once();
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        // Also allow setting id if needed or just use getAttribute
        $user->id = 1;

        $this->authRepository->shouldReceive('createUser')
            ->once()
            ->andReturn($user);

        // Simpler approach: Verify interactions
        $this->authenticationService->shouldReceive('generateTokens')
            ->once()
            ->andReturn(['token' => 'abc']);

        $result = $this->service->registerStudent($dto, '127.0.0.1', 'Mozilla');

        Event::assertDispatched(UserRegistered::class);
        $this->assertEquals(['token' => 'abc'], $result);
    }
}
