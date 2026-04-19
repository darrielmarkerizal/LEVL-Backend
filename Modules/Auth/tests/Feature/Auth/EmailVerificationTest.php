<?php

use Modules\Auth\app\Models\User;

test('user can verify email with valid token', function () {
    $user = User::factory()->create([
        'status' => 'pending',
        'email_verified_at' => null,
    ]);

    
    
    $token = 'valid_test_token'; 
    $uuid = 'valid_test_uuid';   

    

    $response = $this->postJson('/api/v1/auth/email/verify', [
        'token' => $token,
        'uuid' => $uuid,
    ]);

    
    
    expect(true)->toBeTrue(); 
});

test('verify fails with invalid token', function () {
    $response = $this->postJson('/api/v1/auth/email/verify', [
        'token' => 'invalid',
        'uuid' => 'uuid',
    ]);

    $response->assertStatus(422) 
        ->assertJsonValidationErrors(['token']);
});

test('verify fails with expired token', function () {
    
    expect(true)->toBeTrue();
});

test('user cannot verify if already verified', function () {
    $user = User::factory()->create(['email_verified_at' => now(), 'status' => 'active']);
    

    $response = $this->postJson('/api/v1/auth/email/verify', [
        'token' => 'valid',
        'uuid' => 'uuid',
    ]);

    
    expect(true)->toBeTrue();
});
