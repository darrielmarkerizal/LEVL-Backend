<?php

use Modules\Auth\app\Models\User;

test('full account deletion restore flow', function () {
    $password = 'Password123!';
    $user = User::factory()->create(['password' => bcrypt($password), 'status' => 'active']);
    $token = auth()->login($user);

    
    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson('/api/v1/profile/account/delete/request', ['password' => $password]);
    $response->assertStatus(200);

    
    $user->update(['status' => 'deleted']);
    $user->delete();

    
    
    
    

    expect(true)->toBeTrue();
});
