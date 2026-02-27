<?php

declare(strict_types=1);

namespace Modules\Gamification\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\User;
use Modules\Gamification\Models\Point;
use Modules\Gamification\Services\GamificationService;
use Modules\Learning\Models\Assignment;

uses(RefreshDatabase::class);

test('Property 1: One-time XP award per assignment', function () {
    $user = User::factory()->create();
    $assignment = Assignment::factory()->create([
        'allow_multiple' => false,
    ]);
    
    $gamificationService = app(GamificationService::class);
    
    $result1 = $gamificationService->awardXp(
        $user->id,
        50,
        'achievement',
        'assignment',
        $assignment->id,
        [
            'description' => 'Assignment completion XP',
            'allow_multiple' => false,
        ]
    );
    
    expect($result1)->not->toBeNull();
    
    $result2 = $gamificationService->awardXp(
        $user->id,
        50,
        'achievement',
        'assignment',
        $assignment->id,
        [
            'description' => 'Assignment completion XP',
            'allow_multiple' => false,
        ]
    );
    
    expect($result2)->toBeNull();
    
    $count = Point::where('user_id', $user->id)
        ->where('source_type', 'assignment')
        ->where('source_id', $assignment->id)
        ->where('reason', 'achievement')
        ->count();
    
    expect($count)->toBe(1);
})->repeat(100);
