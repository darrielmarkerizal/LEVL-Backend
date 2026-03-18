<?php

declare(strict_types=1);

namespace Modules\Gamification\Tests\Unit;

use Modules\Gamification\Services\LevelService;
use Tests\TestCase;

class LevelServiceTest extends TestCase
{
    private LevelService $levelService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->levelService = app(LevelService::class);
    }

    /** @test */
    public function it_calculates_xp_for_level_1_correctly()
    {
        $xp = $this->levelService->calculateXpForLevel(1);

        $this->assertEquals(100, $xp);
    }

    /** @test */
    public function it_calculates_xp_for_level_2_correctly()
    {
        $xp = $this->levelService->calculateXpForLevel(2);

        // 100 × 2^1.6 = 303.14... ≈ 303
        $this->assertEquals(303, $xp);
    }

    /** @test */
    public function it_calculates_xp_for_level_10_correctly()
    {
        $xp = $this->levelService->calculateXpForLevel(10);

        // 100 × 10^1.6 = 3981.07... ≈ 3981
        $this->assertEquals(3981, $xp);
    }

    /** @test */
    public function it_calculates_xp_for_level_50_correctly()
    {
        $xp = $this->levelService->calculateXpForLevel(50);

        // 100 × 50^1.6 = 78446.45... ≈ 78446
        $this->assertEquals(78446, $xp);
    }

    /** @test */
    public function it_calculates_xp_for_level_100_correctly()
    {
        $xp = $this->levelService->calculateXpForLevel(100);

        // 100 × 100^1.6 = 264575.13... ≈ 264575
        $this->assertEquals(264575, $xp);
    }

    /** @test */
    public function it_returns_zero_for_level_zero()
    {
        $xp = $this->levelService->calculateXpForLevel(0);

        $this->assertEquals(0, $xp);
    }

    /** @test */
    public function it_calculates_level_from_xp_correctly()
    {
        // Level 1: 100 XP
        $this->assertEquals(1, $this->levelService->calculateLevelFromXp(100));

        // Level 2: 403 XP total (100 + 303)
        $this->assertEquals(2, $this->levelService->calculateLevelFromXp(403));

        // Level 10: 20,433 XP total
        $this->assertEquals(10, $this->levelService->calculateLevelFromXp(20433));

        // Level 50: 1,197,126 XP total
        $this->assertEquals(50, $this->levelService->calculateLevelFromXp(1197126));
    }

    /** @test */
    public function it_returns_zero_level_for_zero_xp()
    {
        $level = $this->levelService->calculateLevelFromXp(0);

        $this->assertEquals(0, $level);
    }

    /** @test */
    public function it_calculates_level_from_partial_xp()
    {
        // 50,000 XP should be level 14
        $level = $this->levelService->calculateLevelFromXp(50000);

        $this->assertEquals(14, $level);
    }

    /** @test */
    public function it_calculates_total_xp_for_level()
    {
        // Level 1: 100 XP
        $this->assertEquals(100, $this->levelService->calculateTotalXpForLevel(1));

        // Level 2: 100 + 303 = 403 XP
        $this->assertEquals(403, $this->levelService->calculateTotalXpForLevel(2));

        // Level 10: 20,433 XP
        $this->assertEquals(20433, $this->levelService->calculateTotalXpForLevel(10));
    }

    /** @test */
    public function it_returns_zero_total_xp_for_level_zero()
    {
        $totalXp = $this->levelService->calculateTotalXpForLevel(0);

        $this->assertEquals(0, $totalXp);
    }

    /** @test */
    public function it_gets_level_progress_correctly()
    {
        // User has 50,000 XP (level 14)
        $progress = $this->levelService->getLevelProgress(50000);

        $this->assertEquals(14, $progress['current_level']);
        $this->assertEquals(50000, $progress['total_xp']);
        $this->assertGreaterThan(0, $progress['current_level_xp']);
        $this->assertGreaterThan(0, $progress['xp_to_next_level']);
        $this->assertGreaterThan(0, $progress['xp_required_for_next_level']);
        $this->assertGreaterThanOrEqual(0, $progress['progress_percentage']);
        $this->assertLessThanOrEqual(100, $progress['progress_percentage']);
    }

    /** @test */
    public function it_gets_level_progress_for_exact_level()
    {
        // User has exactly 20,433 XP (exactly level 10)
        $progress = $this->levelService->getLevelProgress(20433);

        $this->assertEquals(10, $progress['current_level']);
        $this->assertEquals(20433, $progress['total_xp']);
        $this->assertEquals(0, $progress['current_level_xp']);
        $this->assertGreaterThan(0, $progress['xp_to_next_level']);
    }

    /** @test */
    public function it_generates_level_configs()
    {
        $configs = $this->levelService->generateLevelConfigs(1, 10);

        $this->assertCount(10, $configs);

        $firstLevel = $configs->first();
        $this->assertEquals(1, $firstLevel['level']);
        $this->assertEquals(100, $firstLevel['xp_required']);
        $this->assertArrayHasKey('name', $firstLevel);
        $this->assertArrayHasKey('rewards', $firstLevel);

        $lastLevel = $configs->last();
        $this->assertEquals(10, $lastLevel['level']);
        $this->assertEquals(3981, $lastLevel['xp_required']);
    }

    /** @test */
    public function it_assigns_correct_level_names()
    {
        $configs = $this->levelService->generateLevelConfigs(1, 100);

        // Level 1-9: Beginner
        $this->assertEquals('Beginner', $configs->firstWhere('level', 5)['name']);

        // Level 10-19: Novice
        $this->assertEquals('Novice', $configs->firstWhere('level', 15)['name']);

        // Level 20-29: Competent
        $this->assertEquals('Competent', $configs->firstWhere('level', 25)['name']);

        // Level 30-39: Intermediate
        $this->assertEquals('Intermediate', $configs->firstWhere('level', 35)['name']);

        // Level 50-59: Advanced
        $this->assertEquals('Advanced', $configs->firstWhere('level', 55)['name']);

        // Level 90+: Legendary Master
        $this->assertEquals('Legendary Master', $configs->firstWhere('level', 95)['name']);
    }

    /** @test */
    public function it_assigns_milestone_rewards()
    {
        $configs = $this->levelService->generateLevelConfigs(1, 100);

        // Level 10 (milestone)
        $level10 = $configs->firstWhere('level', 10);
        $this->assertArrayHasKey('badge', $level10['rewards']);
        $this->assertEquals('level_10_milestone', $level10['rewards']['badge']);
        $this->assertEquals(100, $level10['rewards']['bonus_xp']);

        // Level 50 (major milestone)
        $level50 = $configs->firstWhere('level', 50);
        $this->assertArrayHasKey('badge', $level50['rewards']);
        $this->assertArrayHasKey('title', $level50['rewards']);
        $this->assertEquals('level_50_milestone', $level50['rewards']['badge']);
        $this->assertEquals('Advanced', $level50['rewards']['title']);
        $this->assertEquals(1000, $level50['rewards']['bonus_xp']);

        // Level 15 (not milestone)
        $level15 = $configs->firstWhere('level', 15);
        $this->assertEmpty($level15['rewards']);
    }

    /** @test */
    public function it_calculates_consistent_level_progression()
    {
        // Test that level calculation is consistent
        for ($level = 1; $level <= 100; $level++) {
            $totalXp = $this->levelService->calculateTotalXpForLevel($level);
            $calculatedLevel = $this->levelService->calculateLevelFromXp($totalXp);

            $this->assertEquals(
                $level,
                $calculatedLevel,
                "Level {$level} with {$totalXp} XP should calculate back to level {$level}"
            );
        }
    }

    /** @test */
    public function it_handles_large_xp_values()
    {
        // Test with very large XP (beyond level 100)
        $largeXp = 10000000; // 10 million XP
        $level = $this->levelService->calculateLevelFromXp($largeXp);

        $this->assertGreaterThan(100, $level);
        $this->assertIsInt($level);
    }

    /** @test */
    public function level_progression_is_exponential()
    {
        $level10Xp = $this->levelService->calculateXpForLevel(10);
        $level20Xp = $this->levelService->calculateXpForLevel(20);
        $level40Xp = $this->levelService->calculateXpForLevel(40);

        // XP should grow exponentially
        $this->assertGreaterThan($level10Xp * 2, $level20Xp);
        $this->assertGreaterThan($level20Xp * 2, $level40Xp);
    }

    /** @test */
    public function progress_percentage_is_accurate()
    {
        // Test at 50% progress
        $level10Total = $this->levelService->calculateTotalXpForLevel(10);
        $level11Total = $this->levelService->calculateTotalXpForLevel(11);
        $midpointXp = $level10Total + (int) (($level11Total - $level10Total) / 2);

        $progress = $this->levelService->getLevelProgress($midpointXp);

        $this->assertEquals(10, $progress['current_level']);
        $this->assertGreaterThan(45, $progress['progress_percentage']);
        $this->assertLessThan(55, $progress['progress_percentage']);
    }
}
