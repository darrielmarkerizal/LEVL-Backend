<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Models\User;
use Modules\Common\Models\Category;
use Spatie\Permission\Models\Role;

class InstructorSpecializationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting InstructorSpecializationSeeder...');

        // Get Instructor role
        $this->command->info('Checking for Instructor role...');
        $instructorRole = Role::where('name', 'Instructor')->first();

        if (! $instructorRole) {
            $this->command->warn('Instructor role not found. Skipping seeder.');
            return;
        }

        // Get active categories from database
        $this->command->info('Fetching active categories...');
        $categoryIds = Category::where('status', 'active')
            ->pluck('id')
            ->toArray();

        if (empty($categoryIds)) {
            $this->command->warn('No active categories found. Please run CategorySeeder first.');
            return;
        }

        $this->command->info('Found ' . count($categoryIds) . ' active categories.');

        // Count instructors
        $this->command->info('Counting instructors...');
        $totalInstructors = User::role('Instructor')->count();

        if ($totalInstructors === 0) {
            $this->command->info('No instructors found. Skipping seeder.');
            return;
        }

        $this->command->info("Found {$totalInstructors} instructor(s). Processing...");

        $updated = 0;

        // Process in chunks to avoid memory issues
        User::role('Instructor')
            ->whereNull('specialization_id')
            ->chunk(100, function ($instructors) use ($categoryIds, &$updated) {
                foreach ($instructors as $instructor) {
                    $instructor->specialization_id = $categoryIds[array_rand($categoryIds)];
                    $instructor->save();
                    $updated++;
                }
                $this->command->info("Processed {$updated} instructor(s)...");
            });

        $this->command->info("✓ Updated {$updated} instructor(s) with random specializations from " . count($categoryIds) . " available categories.");
    }
}
