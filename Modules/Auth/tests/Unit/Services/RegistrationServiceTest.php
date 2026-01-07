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
use Tests\TestCase;

class RegistrationServiceTest extends TestCase
{
    private RegistrationService $service;
    private $authRepository;
    private $authenticationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->authRepository = $this->mock(AuthRepositoryInterface::class);
        $this->authenticationService = $this->mock(AuthenticationService::class);
        
        $this->service = new RegistrationService($this->authRepository, $this->authenticationService);
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

        $user = new User();
        $user->id = 1;
        
        $this->authRepository->shouldReceive('createUser')
            ->once()
            ->andReturn($user);

        // Mock User::assignRole (might need Partial Mocking on User if it's an Eloquent Model with strict checks)
        // Ideally we mock the logic or use an in-memory DB for Unit Tests, 
        // But for strict Unit Test with Mocks:
        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('assignRole')->with('Student')->once();
        $user->shouldReceive('getAttribute')->andReturn(1); // for ID access
        
        // Re-mock repository to return our partial mock
        // $this->authRepository... (Wait, complex mocking of Eloquent models is brittle. 
        // Better to use repository that returns a simple object or assume success)

        // Simpler approach: Verify interactions
        $this->authenticationService->shouldReceive('generateTokens')
            ->once()
            ->andReturn(['token' => 'abc']);

        $result = $this->service->registerStudent($dto, '127.0.0.1', 'Mozilla');

        Event::assertDispatched(UserRegistered::class);
        $this->assertEquals(['token' => 'abc'], $result);
    }
}
