<?php

use Modules\Auth\app\Models\User;

test('full registration to verified login flow', function () {
    
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Flow User',
        'username' => 'flowuser',
        'email' => 'flow@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);
    $response->assertStatus(201);

    
    $user = User::where('email', 'flow@example.com')->first();
    $user->forceFill(['email_verified_at' => now(), 'status' => 'active'])->save();

    
    $response = $this->postJson('/api/v1/auth/login', [
        'login' => 'flow@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(200);
    $status = $response->json('data.user.status');
    expect($status)->toBe('active');
});
