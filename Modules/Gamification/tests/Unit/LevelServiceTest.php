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

    
    public function it_calculates_xp_for_level_1_correctly()
    {
        $xp = $this->levelService->calculateXpForLevel(1);

        $this->assertEquals(100, $xp);
    }

    
    public function it_calculates_xp_for_level_2_correctly()
    {
        $xp = $this->levelService->calculateXpForLevel(2);

        
        $this->assertEquals(303, $xp);
    }

    
    public function it_calculates_xp_for_level_10_correctly()
    {
        $xp = $this->levelService->calculateXpForLevel(10);

        
        $this->assertEquals(3981, $xp);
    }

    
    public function it_calculates_xp_for_level_50_correctly()
    {
        $xp = $this->levelService->calculateXpForLevel(50);

        
        $this->assertEquals(78446, $xp);
    }

    
    public function it_calculates_xp_for_level_100_correctly()
    {
        $xp = $this->levelService->calculateXpForLevel(100);

        
        $this->assertEquals(264575, $xp);
    }

    
    public function it_returns_zero_for_level_zero()
    {
        $xp = $this->levelService->calculateXpForLevel(0);

        $this->assertEquals(0, $xp);
    }

    
    public function it_calculates_level_from_xp_correctly()
    {
        
        $this->assertEquals(1, $this->levelService->calculateLevelFromXp(100));

        
        $this->assertEquals(2, $this->levelService->calculateLevelFromXp(403));

        
        $this->assertEquals(10, $this->levelService->calculateLevelFromXp(20433));

        
        $this->assertEquals(50, $this->levelService->calculateLevelFromXp(1197126));
    }

    
    public function it_returns_zero_level_for_zero_xp()
    {
        $level = $this->levelService->calculateLevelFromXp(0);

        $this->assertEquals(0, $level);
    }

    
    public function it_calculates_level_from_partial_xp()
    {
        
        $level = $this->levelService->calculateLevelFromXp(50000);

        $this->assertEquals(14, $level);
    }

    
    public function it_calculates_total_xp_for_level()
    {
        
        $this->assertEquals(100, $this->levelService->calculateTotalXpForLevel(1));

        
        $this->assertEquals(403, $this->levelService->calculateTotalXpForLevel(2));

        
        $this->assertEquals(20433, $this->levelService->calculateTotalXpForLevel(10));
    }

    
    public function it_returns_zero_total_xp_for_level_zero()
    {
        $totalXp = $this->levelService->calculateTotalXpForLevel(0);

        $this->assertEquals(0, $totalXp);
    }

    
    public function it_gets_level_progress_correctly()
    {
        
        $progress = $this->levelService->getLevelProgress(50000);

        $this->assertEquals(14, $progress['current_level']);
        $this->assertEquals(50000, $progress['total_xp']);
        $this->assertGreaterThan(0, $progress['current_level_xp']);
        $this->assertGreaterThan(0, $progress['xp_to_next_level']);
        $this->assertGreaterThan(0, $progress['xp_required_for_next_level']);
        $this->assertGreaterThanOrEqual(0, $progress['progress_percentage']);
        $this->assertLessThanOrEqual(100, $progress['progress_percentage']);
    }

    
    public function it_gets_level_progress_for_exact_level()
    {
        
        $progress = $this->levelService->getLevelProgress(20433);

        $this->assertEquals(10, $progress['current_level']);
        $this->assertEquals(20433, $progress['total_xp']);
        $this->assertEquals(0, $progress['current_level_xp']);
        $this->assertGreaterThan(0, $progress['xp_to_next_level']);
    }

    
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

    
    public function it_assigns_correct_level_names()
    {
        $configs = $this->levelService->generateLevelConfigs(1, 100);

        
        $this->assertEquals('Beginner', $configs->firstWhere('level', 5)['name']);

        
        $this->assertEquals('Novice', $configs->firstWhere('level', 15)['name']);

        
        $this->assertEquals('Competent', $configs->firstWhere('level', 25)['name']);

        
        $this->assertEquals('Intermediate', $configs->firstWhere('level', 35)['name']);

        
        $this->assertEquals('Advanced', $configs->firstWhere('level', 55)['name']);

        
        $this->assertEquals('Legendary Master', $configs->firstWhere('level', 95)['name']);
    }

    
    public function it_assigns_milestone_rewards()
    {
        $configs = $this->levelService->generateLevelConfigs(1, 100);

        
        $level10 = $configs->firstWhere('level', 10);
        $this->assertArrayHasKey('badge', $level10['rewards']);
        $this->assertEquals('level_10_milestone', $level10['rewards']['badge']);
        $this->assertEquals(100, $level10['rewards']['bonus_xp']);

        
        $level50 = $configs->firstWhere('level', 50);
        $this->assertArrayHasKey('badge', $level50['rewards']);
        $this->assertArrayHasKey('title', $level50['rewards']);
        $this->assertEquals('level_50_milestone', $level50['rewards']['badge']);
        $this->assertEquals('Advanced', $level50['rewards']['title']);
        $this->assertEquals(1000, $level50['rewards']['bonus_xp']);

        
        $level15 = $configs->firstWhere('level', 15);
        $this->assertEmpty($level15['rewards']);
    }

    
    public function it_calculates_consistent_level_progression()
    {
        
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

    
    public function it_handles_large_xp_values()
    {
        
        $largeXp = 10000000; 
        $level = $this->levelService->calculateLevelFromXp($largeXp);

        $this->assertGreaterThan(100, $level);
        $this->assertIsInt($level);
    }

    
    public function level_progression_is_exponential()
    {
        $level10Xp = $this->levelService->calculateXpForLevel(10);
        $level20Xp = $this->levelService->calculateXpForLevel(20);
        $level40Xp = $this->levelService->calculateXpForLevel(40);

        
        $this->assertGreaterThan($level10Xp * 2, $level20Xp);
        $this->assertGreaterThan($level20Xp * 2, $level40Xp);
    }

    
    public function progress_percentage_is_accurate()
    {
        
        $level10Total = $this->levelService->calculateTotalXpForLevel(10);
        $level11Total = $this->levelService->calculateTotalXpForLevel(11);
        $midpointXp = $level10Total + (int) (($level11Total - $level10Total) / 2);

        $progress = $this->levelService->getLevelProgress($midpointXp);

        $this->assertEquals(10, $progress['current_level']);
        $this->assertGreaterThan(45, $progress['progress_percentage']);
        $this->assertLessThan(55, $progress['progress_percentage']);
    }
}
