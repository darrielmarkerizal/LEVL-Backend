<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class TestRolesSeeder extends Seeder
{
    
    public function run(): void
    {
        $guard = 'api';

        
        $superadmin = Role::firstOrCreate([
            'name' => 'Superadmin',
            'guard_name' => $guard,
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => $guard,
        ]);

        $instructor = Role::firstOrCreate([
            'name' => 'Instructor',
            'guard_name' => $guard,
        ]);

        $student = Role::firstOrCreate([
            'name' => 'Student',
            'guard_name' => $guard,
        ]);

        
        $permissions = [
            
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            
            'courses.view',
            'courses.create',
            'courses.update',
            'courses.delete',
            'courses.publish',

            
            'enrollments.view',
            'enrollments.create',
            'enrollments.update',
            'enrollments.delete',

            
            'assignments.view',
            'assignments.create',
            'assignments.update',
            'assignments.delete',
            'assignments.grade',

            
            'content.view',
            'content.create',
            'content.update',
            'content.delete',
            'content.publish',

            
            'forums.view',
            'forums.create',
            'forums.update',
            'forums.delete',
            'forums.moderate',

            
            'grades.view',
            'grades.create',
            'grades.update',

            
            'reports.view',
            'reports.generate',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => $guard,
            ]);
        }

        

        
        $superadmin->syncPermissions(Permission::where('guard_name', $guard)->get());

        
        $admin->syncPermissions([
            'users.view',
            'users.create',
            'users.update',
            'courses.view',
            'courses.create',
            'courses.update',
            'courses.delete',
            'courses.publish',
            'enrollments.view',
            'enrollments.create',
            'enrollments.update',
            'enrollments.delete',
            'assignments.view',
            'assignments.create',
            'assignments.update',
            'assignments.delete',
            'assignments.grade',
            'content.view',
            'content.create',
            'content.update',
            'content.delete',
            'content.publish',
            'forums.view',
            'forums.create',
            'forums.update',
            'forums.delete',
            'forums.moderate',
            'grades.view',
            'grades.create',
            'grades.update',
            'reports.view',
            'reports.generate',
        ]);

        
        $instructor->syncPermissions([
            'courses.view',
            'courses.create',
            'courses.update',
            'courses.publish',
            'enrollments.view',
            'assignments.view',
            'assignments.create',
            'assignments.update',
            'assignments.delete',
            'assignments.grade',
            'content.view',
            'content.create',
            'content.update',
            'forums.view',
            'forums.create',
            'forums.update',
            'forums.moderate',
            'grades.view',
            'grades.create',
            'grades.update',
            'reports.view',
        ]);

        
        $student->syncPermissions([
            'courses.view',
            'enrollments.view',
            'enrollments.create',
            'assignments.view',
            'content.view',
            'forums.view',
            'forums.create',
            'forums.update',
            'grades.view',
        ]);
    }
}
