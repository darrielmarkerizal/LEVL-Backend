<?php

namespace Modules\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Auth\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Modules\Auth\Jobs\ExportUsersToEmailJob;

class UserBulkApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'Superadmin', 'guard_name' => 'api']);
        Role::create(['name' => 'Admin', 'guard_name' => 'api']);
    }

    public function test_bulk_export_dispatches_job(): void
    {
        Queue::fake();

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $users = User::factory()->count(3)->create();
        $userIds = $users->pluck('id')->toArray();

        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/v1/users/bulk/export', [
                'user_ids' => $userIds,
                'email' => 'test@example.com'
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        Queue::assertPushed(ExportUsersToEmailJob::class);
    }

    public function test_bulk_export_validation_error(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/v1/users/bulk/export', [
                'email' => 'not-an-email'
            ]);

        $response->assertStatus(422);
    }
}
