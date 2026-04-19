<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Auth\app\Models\User;

test('full password reset flow', function () {
    
    $user = User::factory()->create(['email' => 'reset@example.com', 'password' => Hash::make('OldPass')]);

    $this->postJson('/api/v1/auth/password/forgot', ['login' => 'reset@example.com'])
        ->assertStatus(200);

    
    $plainToken = Str::random(64);
    DB::table('password_reset_tokens')->where('email', 'reset@example.com')->update([
        'token' => Hash::make($plainToken),
    ]);

    
    $this->postJson('/api/v1/auth/password/forgot/confirm', [
        'token' => $plainToken,
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ])->assertStatus(200);

    
    $this->postJson('/api/v1/auth/login', [
        'login' => 'reset@example.com',
        'password' => 'NewPassword123!',
    ])->assertStatus(200);
});
