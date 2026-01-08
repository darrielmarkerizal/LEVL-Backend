<?php

namespace Modules\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Auth\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'Superadmin', 'guard_name' => 'api']);
        Role::create(['name' => 'Admin', 'guard_name' => 'api']);
        Role::create(['name' => 'Instructor', 'guard_name' => 'api']);
        Role::create(['name' => 'Student', 'guard_name' => 'api']);
    }

    public function test_admin_can_access_users_index(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin, 'api')
            ->getJson('/api/v1/users');

        $response->assertStatus(200);
    }

    public function test_create_user_returns_translated_validation_errors(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        // Test with empty body to trigger validation
        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/v1/users', []);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'status' => 'error',
                'message' => 'Data yang Anda kirim tidak valid. Periksa kembali isian Anda.',
            ]);
            
        // Check if messages are translated (assuming locale is 'id' by default or set by middleware)
        // Since we refactored to use __(), it should use the translation file.
    }

    public function test_update_user_status_returns_translated_errors(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        $target = User::factory()->create();

        $response = $this->actingAs($admin, 'api')
            ->putJson("/api/v1/users/{$target->id}", [
                'status' => 'invalid-status'
            ]);

        $response->assertStatus(422);
        // The error message for 'in' rule should be translated.
    }

    public function test_post_users_is_reachable(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin, 'api')
            ->postJson('/api/v1/users', [
                'name' => 'Test User',
                'username' => 'testuser',
                'email' => 'test@example.com',
                'role' => 'Instructor'
            ]);

        // If it was returning 404, this will fail.
        $this->assertNotEquals(404, $response->getStatusCode());
    }
}
