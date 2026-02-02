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
        $badges = Badge::whereIn('code', ['first_step', 'rookie'])->get()->keyBy('code');

        foreach ($students as $student) {
            // Award 'First Step' badge
            if ($badges->has('first_step') && !$this->badgeManager->hasBadge($student->id, 'first_step')) {
                $this->badgeManager->awardBadge(
                    $student->id,
                    'first_step',
                    $badges['first_step']->name,
                    $badges['first_step']->description
                );
            }

            // Award 'Rookie' badge (just as an example/default set)
            if ($badges->has('rookie') && !$this->badgeManager->hasBadge($student->id, 'rookie')) {
                $this->badgeManager->awardBadge(
                    $student->id,
                    'rookie',
                    $badges['rookie']->name,
                    $badges['rookie']->description
                );
            }
        }
    }
}
