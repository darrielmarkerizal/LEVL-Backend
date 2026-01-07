<?php

declare(strict_types=1);

namespace Modules\Auth\Tests\Unit\Services;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Contracts\Repositories\AuthRepositoryInterface;
use Modules\Auth\DTOs\LoginDTO;
use Modules\Auth\Events\UserLoggedIn;
use Modules\Auth\Models\JwtRefreshToken;
use Modules\Auth\Models\User;
use Modules\Auth\Services\AuthenticationService;
use Modules\Auth\Services\LoginThrottlingService;
use Tests\TestCase;
use Tymon\JWTAuth\Factory;
use Tymon\JWTAuth\JWTAuth;

class AuthenticationServiceTest extends TestCase
{
    private AuthenticationService $service;
    private $authRepository;
    private $jwt;
    private $throttle;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->authRepository = $this->mock(AuthRepositoryInterface::class);
        $this->jwt = $this->mock(JWTAuth::class);
        $this->throttle = $this->mock(LoginThrottlingService::class);
        
        $this->service = new AuthenticationService($this->authRepository, $this->jwt, $this->throttle);
    }

    public function test_login_success()
    {
        Event::fake();

        $dto = new LoginDTO(login: 'student@test.com', password: 'password');
        
        $user = new User();
        $user->id = 1;
        $user->password = Hash::make('password');
        $user->email = 'student@test.com';

        $this->throttle->shouldReceive('ensureNotLocked')->once();
        $this->throttle->shouldReceive('tooManyAttempts')->andReturn(false);
        $this->throttle->shouldReceive('clearAttempts')->once();

        $this->authRepository->shouldReceive('findByLogin')
            ->once()
            ->andReturn($user);

        // JWT Mocking
        $this->jwt->shouldReceive('fromUser')->andReturn('access_token');
        $factory = \Mockery::mock(Factory::class);
        $factory->shouldReceive('getTTL')->andReturn(60);
        $this->jwt->shouldReceive('factory')->andReturn($factory);

        // Refresh Token Mocking
        $refreshToken = new JwtRefreshToken();
        $refreshToken->setAttribute('plain_token', 'refresh_token');
        
        $this->authRepository->shouldReceive('createRefreshToken')
            ->once()
            ->andReturn($refreshToken);

        // User Role Mocking (Partial)
        // Since we can't easily mock hydration of roles in unit test without DB, 
        // we assume the service handles basic user object effectively or we use a more integrated test for roles.
        // For this unit test, we focus on the orchestration flow.
        
        // Note: The service calls $user->getRoleNames()->values().
        // This will fail on a plain new User() instance without roles relation loaded.
        // We should mock the user method or use a real object with loaded relationship (hard in Unit).
        // Let's use a Mockery partial for User to bypass relation.
        $userMock = \Mockery::mock(User::class)->makePartial();
        $userMock->id = 1;
        $userMock->password = Hash::make('password');
        $userMock->email = 'student@test.com';
        $userMock->shouldReceive('getRoleNames')->andReturn(collect(['Student']));
        $userMock->shouldReceive('toArray')->andReturn(['id' => 1, 'email' => 'student@test.com']);
        
        // Re-bind repository return
        $this->authRepository->shouldReceive('findByLogin')->with('student@test.com')->andReturn($userMock); // Override previous expectation

        $result = $this->service->login($dto, null, '127.0.0.1', 'Mozilla');

        Event::assertDispatched(UserLoggedIn::class);
        $this->assertEquals('access_token', $result['access_token']);
        $this->assertEquals('refresh_token', $result['refresh_token']);
    }
}
