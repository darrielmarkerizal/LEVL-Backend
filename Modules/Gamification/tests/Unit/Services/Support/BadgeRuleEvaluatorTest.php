<?php

namespace Modules\Gamification\Tests\Unit\Services\Support;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Modules\Auth\Models\User;
use Modules\Gamification\Models\Badge;
use Modules\Gamification\Models\BadgeRule;
use Modules\Gamification\Services\Support\BadgeManager;
use Modules\Gamification\Services\Support\BadgeRuleEvaluator;
use Modules\Gamification\Services\Support\PointManager;
use Tests\TestCase;

class BadgeRuleEvaluatorTest extends TestCase
{
    use RefreshDatabase;

    private BadgeRuleEvaluator $evaluator;

    private MockInterface $badgeManagerMock;

    private MockInterface $pointManagerMock;

    private User $user;

    private Badge $badge;

    protected function setUp(): void
    {
        parent::setUp();

        $this->badgeManagerMock = $this->mock(BadgeManager::class);
        $this->pointManagerMock = $this->mock(PointManager::class);

        $this->evaluator = new BadgeRuleEvaluator(
            $this->badgeManagerMock,
            $this->pointManagerMock
        );

        $this->user = User::factory()->create();
        $this->badge = Badge::create(['code' => 'test_badge', 'name' => 'Test Badge']);
    }

    public function test_evaluates_course_slug_trigger(): void
    {
        BadgeRule::create([
            'badge_id' => $this->badge->id,
            'event_trigger' => 'course_completed',
            'conditions' => [
                'course_slug' => 'laravel-101',
            ],
        ]);

        $this->badgeManagerMock->shouldReceive('awardBadge')->once()->with(
            $this->user->id,
            'test_badge',
            'Test Badge',
            \Mockery::any()
        );

        // Correct slug
        $this->evaluator->evaluate($this->user, 'course_completed', ['course_slug' => 'laravel-101']);
    }

    public function test_rejects_course_slug_if_not_match(): void
    {
        BadgeRule::create([
            'badge_id' => $this->badge->id,
            'event_trigger' => 'course_completed',
            'conditions' => [
                'course_slug' => 'laravel-101',
            ],
        ]);

        $this->badgeManagerMock->shouldReceive('awardBadge')->never();

        // Incorrect slug
        $this->evaluator->evaluate($this->user, 'course_completed', ['course_slug' => 'php-101']);
    }

    public function test_evaluates_score_and_attempts(): void
    {
        BadgeRule::create([
            'badge_id' => $this->badge->id,
            'event_trigger' => 'assignment_graded',
            'conditions' => [
                'min_score' => 90,
                'max_attempts' => 1,
            ],
        ]);

        $this->badgeManagerMock->shouldReceive('awardBadge')->once();

        $this->evaluator->evaluate($this->user, 'assignment_graded', [
            'score' => 95,
            'attempts' => 1,
        ]);
    }

    public function test_rejects_low_score(): void
    {
        BadgeRule::create([
            'badge_id' => $this->badge->id,
            'event_trigger' => 'assignment_graded',
            'conditions' => [
                'min_score' => 90,
            ],
        ]);

        $this->badgeManagerMock->shouldReceive('awardBadge')->never();

        $this->evaluator->evaluate($this->user, 'assignment_graded', [
            'score' => 85,
        ]);
    }

    public function test_evaluates_habit_login_time(): void
    {
        BadgeRule::create([
            'badge_id' => $this->badge->id,
            'event_trigger' => 'login',
            'conditions' => [
                'time_before' => '06:00:00',
            ],
        ]);

        $this->badgeManagerMock->shouldReceive('awardBadge')->once();

        $this->evaluator->evaluate($this->user, 'login', [
            'time' => '05:30:00',
        ]);
    }

    public function test_rejects_habit_login_late_time(): void
    {
        BadgeRule::create([
            'badge_id' => $this->badge->id,
            'event_trigger' => 'login',
            'conditions' => [
                'time_before' => '06:00:00',
            ],
        ]);

        $this->badgeManagerMock->shouldReceive('awardBadge')->never();

        $this->evaluator->evaluate($this->user, 'login', [
            'time' => '07:30:00',
        ]);
    }
}
