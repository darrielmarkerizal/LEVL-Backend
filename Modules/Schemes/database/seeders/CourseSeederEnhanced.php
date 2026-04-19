<?php

declare(strict_types=1);

namespace Modules\Schemes\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Common\Models\Category;
use Modules\Schemes\Enums\CourseStatus;
use Modules\Schemes\Enums\CourseType;
use Modules\Schemes\Enums\EnrollmentType;
use Modules\Schemes\Enums\LevelTag;
use Modules\Schemes\Models\Course;
use Modules\Schemes\Models\Tag;

class CourseSeederEnhanced extends Seeder
{
    private const CHUNK_SIZE = 20;

    private const TOTAL_COURSES = 50;

    private int $courseSeq = 0;

    public function run(): void
    {
        $this->courseSeq = 0;
        $this->command->info("\n📚 Creating realistic courses...");

        $instructors = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Instructor', 'Admin', 'Superadmin']);
        })->get();

        if ($instructors->isEmpty()) {
            $this->command->warn('  ⚠️  No instructors found. Please run UserSeeder first.');

            return;
        }

        $categories = Category::where('status', 'active')->get();
        $tags = Tag::all();

        if ($categories->isEmpty() || $tags->isEmpty()) {
            $this->command->warn('  ⚠️  No categories or tags found. Please run CategorySeeder and TagSeeder first.');

            return;
        }

        $this->command->info('  📊 Creating '.self::TOTAL_COURSES.' courses across all scenarios...');

        $distribution = [
            'published_auto' => 25,       
            'published_approval' => 10,    
            'published_key' => 10,         
            'draft' => 5,                 
        ];

        $created = 0;
        foreach ($distribution as $scenario => $count) {
            $this->command->info("\n  📝 Scenario: {$scenario} ({$count} courses)");
            $scenarioCourses = $this->createCoursesForScenario($scenario, $count, $categories, $instructors);

            $this->assignInstructorsToCourses($scenarioCourses, $instructors);
            $this->attachTagsToCourses($scenarioCourses, $tags);
            $this->createCourseOutcomes($scenarioCourses);
            $this->attachMediaToCourses($scenarioCourses);

            $created += $scenarioCourses->count();
            $this->command->info("    ✓ Created {$scenarioCourses->count()} courses for {$scenario}");
        }

        $total = Course::count();
        $this->command->info("\n✅ Course seeding completed!");
        $this->command->info("   📊 Total courses: {$total}");
        $this->printCourseSummary();
    }

    private function createCoursesForScenario(string $scenario, int $count, $categories, $instructors): \Illuminate\Support\Collection
    {
        $courseSeq = &$this->courseSeq;
        
        $state = function () use ($categories, $instructors, &$courseSeq) {
            $courseSeq++;
            $i = $courseSeq - 1;

            return [
                'category_id' => $categories[$i % $categories->count()]->id,
                'instructor_id' => $instructors[$i % $instructors->count()]->id,
            ];
        };

        $factory = Course::factory()->count($count)->state($state);

        return match ($scenario) {
            'published_auto' => $factory->published()->openEnrollment()->create(),
            'published_approval' => $factory->published()->state([
                'enrollment_type' => EnrollmentType::Approval->value,
            ])->create(),
            'published_key' => $factory->published()->state([
                'enrollment_type' => EnrollmentType::KeyBased->value,
                'enrollment_key' => $this->generateEnrollmentKey(),
            ])->create(),
            'draft' => $factory->draft()->create(),
            default => collect(),
        };
    }

    private function generateEnrollmentKey(): string
    {
        $plainKey = \Illuminate\Support\Str::random(12);

        return bcrypt($plainKey);
    }

    private function assignInstructorsToCourses($courses, $instructors): void
    {
        if ($courses->isEmpty() || $instructors->isEmpty()) {
            return;
        }

        foreach ($courses as $course) {
            $numInstructors = 1 + ($course->id % 3);
            $selectedInstructors = $instructors->take(min($numInstructors, $instructors->count()));

            
            $instructorIds = $selectedInstructors->pluck('id')->toArray();
            $course->instructors()->syncWithoutDetaching($instructorIds);
        }
    }

    private function attachTagsToCourses($courses, $tags): void
    {
        if ($courses->isEmpty() || $tags->isEmpty()) {
            return;
        }

        foreach ($courses as $course) {
            $numTags = 3 + ($course->id % 6);
            $selectedTags = $tags->take(min($numTags, $tags->count()));

            if ($selectedTags instanceof Tag) {
                $selectedTags = collect([$selectedTags]);
            }

            
            $tagIds = $selectedTags->pluck('id')->toArray();
            $course->tags()->syncWithoutDetaching($tagIds);
        }
    }

    private function printCourseSummary(): void
    {
        $this->command->info("\n📋 Course Distribution:");

        $byStatus = Course::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        foreach ($byStatus as $stat) {
            $statusValue = (string) ($stat->status instanceof CourseStatus ? $stat->status->value : $stat->status);
            $this->command->info("   • Status '{$statusValue}': {$stat->total}");
        }

        $this->command->info("\n📊 Enrollment Type Distribution:");
        $byEnrollment = Course::select('enrollment_type', DB::raw('count(*) as total'))
            ->groupBy('enrollment_type')
            ->get();

        foreach ($byEnrollment as $stat) {
            $enrollmentValue = (string) ($stat->enrollment_type instanceof EnrollmentType ? $stat->enrollment_type->value : $stat->enrollment_type);
            $this->command->info("   • {$enrollmentValue}: {$stat->total}");
        }

        $this->command->info("\n🎓 Level Distribution:");
        $byLevel = Course::select('level_tag', DB::raw('count(*) as total'))
            ->groupBy('level_tag')
            ->get();

        foreach ($byLevel as $stat) {
            $levelTagValue = (string) ($stat->level_tag instanceof LevelTag ? $stat->level_tag->value : $stat->level_tag);
            $this->command->info("   • {$levelTagValue}: {$stat->total}");
        }

        $this->command->info("\n📦 Type Distribution:");
        $byType = Course::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get();

        foreach ($byType as $stat) {
            $typeValue = (string) ($stat->type instanceof CourseType ? $stat->type->value : $stat->type);
            $this->command->info("   • {$typeValue}: {$stat->total}");
        }
    }

    private function createCourseOutcomes($courses): void
    {
        if ($courses->isEmpty()) {
            return;
        }

        $outcomes = [];
        $outcomeTexts = [
            'Menerapkan prosedur kerja sesuai standar kompetensi.',
            'Menyusun dokumentasi bukti yang dapat diverifikasi asesor.',
            'Mengoperasikan peralatan sesuai instruksi keselamatan.',
            'Berkoordinasi dengan rekan kerja dalam tugas terstruktur.',
            'Mengidentifikasi risiko dan tindakan pencegahan di tempat kerja.',
            'Menyampaikan informasi secara jelas kepada pemangku kepentingan.',
            'Memantau indikator kinerja sesuai target organisasi.',
            'Mengikuti alur asesmen dan jadwal pelatihan yang ditetapkan.',
            'Menggunakan alat bantu digital untuk pelaporan.',
            'Menjaga etika profesi pada interaksi dengan klien.',
        ];

        foreach ($courses as $course) {
            $numOutcomes = 4 + ($course->id % 4);
            for ($order = 0; $order < $numOutcomes; $order++) {
                $idx = ($course->id + $order) % count($outcomeTexts);
                $outcomes[] = [
                    'course_id' => $course->id,
                    'outcome_text' => $outcomeTexts[$idx],
                    'order' => $order,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (! empty($outcomes)) {
            foreach (array_chunk($outcomes, 500) as $chunk) {
                DB::table('course_outcomes')->insertOrIgnore($chunk);
            }
        }
    }

    private function attachMediaToCourses($courses): void
    {
        $this->command->info('  📸 Attaching media to courses (this may take a while)...');
        
        foreach ($courses as $course) {
            try {
                $course->addMediaFromUrl("https://picsum.photos/seed/{$course->id}/300")
                    ->toMediaCollection('thumbnail');

                $course->addMediaFromUrl("https://picsum.photos/seed/{$course->id}/800/600")
                    ->toMediaCollection('banner');
            } catch (\Throwable $e) {
                
                
                continue;
            }
        }
    }
}
