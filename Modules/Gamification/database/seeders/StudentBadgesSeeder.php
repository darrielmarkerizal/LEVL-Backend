<?php

namespace Modules\Gamification\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Models\User;
use Modules\Gamification\Models\Badge;
use Modules\Gamification\Services\Support\BadgeManager;

class StudentBadgesSeeder extends Seeder
{
    public function __construct(private readonly BadgeManager $badgeManager) {}

    public function run(): void
    {
        $students = User::role('Student')->get();
        $badges = Badge::all();

        if ($badges->isEmpty()) {
            $this->command->warn('No badges found. Skipping Student Badges Seeding.');
            return;
        }

        foreach ($students as $student) {
            // First step is given to everyone as a common standard
            $firstStep = $badges->where('code', 'first_step')->first();
            if ($firstStep && ! $this->badgeManager->hasBadge($student->id, 'first_step')) {
                $this->badgeManager->awardBadge(
                    $student->id,
                    'first_step',
                    $firstStep->name,
                    $firstStep->description
                );
            }

            // Assign a random 5-15 badges to each student to simulate varied gamification profiles
            $randomBadges = $badges->where('code', '!=', 'first_step')->random(rand(5, 15));
            foreach ($randomBadges as $badge) {
                if (! $this->badgeManager->hasBadge($student->id, $badge->code)) {
                    $this->badgeManager->awardBadge(
                        $student->id,
                        $badge->code,
                        $badge->name,
                        $badge->description
                    );
                }
            }
        }
    }
}
