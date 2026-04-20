<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Support\SeederDate;
use App\Support\RealisticSeederContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\User;
use Spatie\Permission\Models\Role;

class ActiveUsersSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'Instructor', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'Student', 'guard_name' => 'api']);

        $superadmin = User::query()->updateOrCreate(
            ['username' => 'superadmin'],
            [
                'email' => RealisticSeederContent::demoEmail('superadmin'),
                'name' => 'Rizky Pratama',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            ]
        );
        if (! $superadmin->hasRole('Superadmin')) {
            $superadmin->assignRole('Superadmin');
        }

        $admin = User::query()->updateOrCreate(
            ['username' => 'admin'],
            [
                'email' => RealisticSeederContent::demoEmail('admin'),
                'name' => 'Dian Lestari',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            ]
        );
        if (! $admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }

        $instructor = User::query()->updateOrCreate(
            ['username' => 'instructor'],
            [
                'email' => RealisticSeederContent::demoEmail('instruktur'),
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            ]
        );
        if (! $instructor->hasRole('Instructor')) {
            $instructor->assignRole('Instructor');
        }

        $student = User::query()->updateOrCreate(
            ['username' => 'student'],
            [
                'email' => RealisticSeederContent::demoEmail('peserta'),
                'name' => 'Citra Anggraini',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => SeederDate::randomPastDateTimeBetween(1, 180),
            ]
        );
        if (! $student->hasRole('Student')) {
            $student->assignRole('Student');
        }

        $avatarUrls = fn ($user) => [
            "https://api.dicebear.com/9.x/avataaars/png?seed={$user->username}",
            'https://ui-avatars.com/api/?name='.rawurlencode($user->name).'&size=256&background=random',
        ];
        foreach ([$superadmin, $admin, $instructor, $student] as $user) {
            try {
                if ($user->hasMedia('avatar')) {
                    continue;
                }
            } catch (\Throwable $e) {
                
                continue;
            }
            
            foreach ($avatarUrls($user) as $url) {
                try {
                    $user->addMediaFromUrl($url)->toMediaCollection('avatar');
                    break;
                } catch (\Throwable $e) {
                    $this->command->warn("Avatar URL failed for {$user->username}, trying next: ".$e->getMessage());
                }
            }
        }
    }
}
