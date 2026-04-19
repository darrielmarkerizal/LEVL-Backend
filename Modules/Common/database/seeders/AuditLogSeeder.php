<?php

namespace Modules\Common\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Models\User;
use Spatie\Activitylog\Models\Activity;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        if (! $admin) {
            return;
        }

        $actions = [
            'submission_created',
            'state_transition',
            'grading',
            'grade_override',
        ];

        for ($i = 0; $i < 50; $i++) {
            $action = $actions[array_rand($actions)];

            
            activity()
                ->causedBy($admin)
                ->performedOn($admin) 
                ->withProperties([
                    'assignment_id' => rand(1, 10),
                    'student_id' => rand(1, 5),
                    'old_status' => 'pending',
                    'new_status' => 'submitted',
                    'reason' => 'Seeded data',
                ])
                ->log($action);
        }
    }
}
