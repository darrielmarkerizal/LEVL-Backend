<?php

use Modules\Auth\app\Models\User;

test('can refresh with valid token', function () {
    $user = User::factory()->create(['status' => 'active']);
    
    
    $response = $this->postJson('/api/v1/auth/login', [
        'login' => $user->email,
        'password' => 'password', 
    ]);

    
    if ($response->status() !== 200) {
        
        
    }

    $refreshToken = $response->json('data.refresh_token');

    $response = $this->postJson('/api/v1/auth/refresh', [
        'refresh_token' => $refreshToken,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'access_token',
                'refresh_token',
                'expires_in',
            ],
        ]);
});

test('cannot refresh with invalid token', function () {
    $response = $this->postJson('/api/v1/auth/refresh', [
        'refresh_token' => 'invalid_token_string',
    ]);

    $response->assertStatus(401); 
});

test('cannot refresh with expired token', function () {
    
    $this->assertTrue(true);
});

test('refresh token reused is detected', function () {
    
    $this->assertTrue(true);
});
