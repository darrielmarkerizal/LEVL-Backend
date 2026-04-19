<?php

namespace Tests\Feature\Gamification;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;
use Modules\Gamification\Models\Badge;
use Modules\Gamification\Models\Point;
use Modules\Gamification\Models\UserBadge;
use Modules\Gamification\Models\UserGamificationStat;
use Tests\TestCase;

class GamificationApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    public function test_user_badges_returns_direct_collection()
    {
        $badge = Badge::create([
            'code' => 'TEST_BADGE',
            'name' => 'Test Badge',
        ]);

        UserBadge::create([
            'user_id' => $this->user->id,
            'badge_id' => $badge->id,
            'awarded_at' => now(),
        ]);

        $response = $this->getJson(route('user.gamification.badges'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'badge' => ['code', 'name'],
                        'awarded_at',
                    ],
                ],
            ]);

        
        $this->assertArrayNotHasKey('badges', $response->json('data'));
    }

    public function test_leaderboard_includes_my_rank_meta()
    {
        
        UserGamificationStat::create([
            'user_id' => $this->user->id,
            'total_xp' => 1000,
            'global_level' => 5,
        ]);

        $response = $this->getJson(route('leaderboards.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta' => [
                    'pagination',
                    'my_rank' => [
                        'rank',
                        'total_xp',
                        'level',
                    ],
                ],
            ]);

        $this->assertEquals(1000, $response->json('meta.my_rank.total_xp'));
    }

    public function test_points_history_returns_correct_structure()
    {
        Point::create([
            'user_id' => $this->user->id,
            'points' => 10,
            'reason' => 'login_streak',
            'source_type' => 'system',
            'source_id' => 1,
        ]);

        $response = $this->getJson(route('user.gamification.points-history'));

        $response->assertStatus(200);
    }
}
