<?php

namespace Tests\Traits;

use Illuminate\Support\Str;
use Modules\Auth\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;


trait WithAuthentication
{
    
    protected function authenticateAs(string $role, array $attributes = []): User
    {
        $this->ensureRolesExist();

        $user = User::factory()->active()->create(array_merge([
            'email' => 'trait-user-'.Str::uuid().'@example.test',
            'username' => 'trait_user_'.Str::lower(Str::random(16)),
        ], $attributes));
        $user->assignRole($role);
        $this->actingAs($user, 'api');

        return $user;
    }

    
    protected function authenticateAsSuperadmin(array $attributes = []): User
    {
        return $this->authenticateAs('Superadmin', $attributes);
    }

    
    protected function authenticateAsAdmin(array $attributes = []): User
    {
        return $this->authenticateAs('Admin', $attributes);
    }

    
    protected function authenticateAsInstructor(array $attributes = []): User
    {
        return $this->authenticateAs('Instructor', $attributes);
    }

    
    protected function authenticateAsStudent(array $attributes = []): User
    {
        return $this->authenticateAs('Student', $attributes);
    }

    
    protected function ensureRolesExist(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'api';
        $roles = ['Superadmin', 'Admin', 'Instructor', 'Student'];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => $guard,
            ]);
        }
    }

    
    protected function unauthenticate(): void
    {
        $this->app['auth']->forgetGuards();
    }
}
