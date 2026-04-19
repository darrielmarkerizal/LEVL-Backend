<?php

namespace Modules\Schemes\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Models\User;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Lesson;
use Modules\Schemes\Models\Unit;

class CourseSeeder extends Seeder
{
    
    public function run(): void
    {
        \DB::connection()->disableQueryLog();

        echo "Seeding courses and course structure...\n";

        $instructors = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Instructor', 'Admin', 'Superadmin']);
        })->limit(200)->get();

        if ($instructors->isEmpty()) {
            echo "⚠️  No instructors found. Skipping course seeding.\n";

            return;
        }

        
        echo "Creating 20 courses...\n";
        $courses = Course::factory()
            ->count(20)
            ->published()
            ->openEnrollment()
            ->create();

        
        echo "Assigning instructors to courses...\n";
        $this->assignInstructorsToCourses($courses, $instructors);

        
        echo "Creating units and lessons...\n";
        $this->createUnitsAndLessons($courses);

        echo "✅ Course seeding completed!\n";
        echo "Created 20 courses with units and lessons\n";

        gc_collect_cycles();
        \DB::connection()->enableQueryLog();
    }

    
    private function assignInstructorsToCourses($courses, $instructors): void
    {
        $courseUpdates = [];

        foreach ($courses as $course) {
            $mainInstructor = $instructors->random();

            $courseUpdates[] = [
                'id' => $course->id,
                'instructor_id' => $mainInstructor->id,
            ];

            
            $additionalAdminsCount = rand(0, 2);
            if ($additionalAdminsCount > 0) {
                $availableInstructors = $instructors->where('id', '!=', $mainInstructor->id);
                if ($availableInstructors->count() >= $additionalAdminsCount) {
                    $additionalAdmins = $availableInstructors->random($additionalAdminsCount);
                    $course->instructors()->syncWithoutDetaching($additionalAdmins->pluck('id')->toArray());
                }
            }
        }

        foreach ($courseUpdates as $update) {
            \Illuminate\Support\Facades\DB::table('courses')
                ->where('id', $update['id'])
                ->update(['instructor_id' => $update['instructor_id']]);
        }
    }

    
    private function createUnitsAndLessons($courses): void
    {
        $counter = 0;
        foreach ($courses as $course) {
            $unitCount = rand(2, 3);

            $units = Unit::factory()
                ->count($unitCount)
                ->forCourse($course)
                ->create();

            foreach ($units as $unit) {
                $lessonCount = rand(3, 5);
                Lesson::factory()
                    ->count($lessonCount)
                    ->forUnit($unit)
                    ->create();

                $counter++;
                if ($counter % 30 === 0) {
                    gc_collect_cycles();
                }
            }
        }
    }
}
