<?php

use Illuminate\Support\Facades\Queue;
use Modules\Auth\app\Models\User;

test('admin can export users by ids', function () {
    Queue::fake();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $users = User::factory()->count(3)->create();
    $ids = $users->pluck('id')->toArray();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.auth()->login($admin)])
        ->postJson('/api/v1/users/bulk/export', [
            'user_ids' => $ids,
        ]);

    $response->assertStatus(200);
});
