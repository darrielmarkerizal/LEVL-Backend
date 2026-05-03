<?php

use Modules\Auth\app\Models\User;

test('user can view public profile', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $token = auth()->login($userB);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson("/api/v1/users/{$userA->id}/profile");

    $response->assertStatus(200);
});

test('view profile fails without authentication', function () {
    $user = User::factory()->create();
    $response = $this->getJson("/api/v1/users/{$user->id}/profile");
    $response->assertStatus(401);
});

test('owner can view own profile', function () {
    $user = User::factory()->create();
    $token = auth()->login($user);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson("/api/v1/users/{$user->id}/profile");

    $response->assertStatus(200);
});

test('student cannot see phone number of other student profile', function () {
    $studentA = User::factory()->create(['phone' => '081234567890']);
    $studentB = User::factory()->create(['phone' => '089876543210']);
    
    $studentA->assignRole('Student');
    $studentB->assignRole('Student');

    $token = auth()->login($studentB);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson("/api/v1/users/{$studentA->id}/profile");

    $response->assertStatus(200)
        ->assertJsonMissing(['phone' => $studentA->phone]);
});

test('owner can see own phone number', function () {
    $student = User::factory()->create(['phone' => '081234567890']);
    $student->assignRole('Student');

    $token = auth()->login($student);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson("/api/v1/users/{$student->id}/profile");

    $response->assertStatus(200)
        ->assertJsonFragment(['phone' => $student->phone]);
});

test('admin can see student phone number', function () {
    $student = User::factory()->create(['phone' => '081234567890']);
    $admin = User::factory()->create();
    
    $student->assignRole('Student');
    $admin->assignRole('Admin');

    $token = auth()->login($admin);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->getJson("/api/v1/users/{$student->id}/profile");

    $response->assertStatus(200)
        ->assertJsonFragment(['phone' => $student->phone]);
});
