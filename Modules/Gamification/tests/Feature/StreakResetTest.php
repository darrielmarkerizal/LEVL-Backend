<?php

declare(strict_types=1);

use Carbon\Carbon;
use Modules\Auth\Models\User;
use Modules\Gamification\Models\UserGamificationStat;
use Modules\Gamification\Services\Support\StreakResetService;

beforeEach(function () {
    $this->service = app(StreakResetService::class);
});

test('reset streak untuk user yang tidak aktif kemarin', function () {
    $user = User::factory()->create();
    
    UserGamificationStat::factory()->create([
        'user_id' => $user->id,
        'current_streak' => 5,
        'last_activity_date' => Carbon::yesterday()->subDay(),
    ]);

    $resetCount = $this->service->resetInactiveStreaks();

    expect($resetCount)->toBe(1);
    
    $stats = UserGamificationStat::where('user_id', $user->id)->first();
    expect($stats->current_streak)->toBe(0);
});

test('tidak reset streak untuk user yang aktif kemarin', function () {
    $user = User::factory()->create();
    
    UserGamificationStat::factory()->create([
        'user_id' => $user->id,
        'current_streak' => 5,
        'last_activity_date' => Carbon::yesterday(),
    ]);

    $resetCount = $this->service->resetInactiveStreaks();

    expect($resetCount)->toBe(0);
    
    $stats = UserGamificationStat::where('user_id', $user->id)->first();
    expect($stats->current_streak)->toBe(5);
});

test('tidak reset streak untuk user yang aktif hari ini', function () {
    $user = User::factory()->create();
    
    UserGamificationStat::factory()->create([
        'user_id' => $user->id,
        'current_streak' => 3,
        'last_activity_date' => Carbon::today(),
    ]);

    $resetCount = $this->service->resetInactiveStreaks();

    expect($resetCount)->toBe(0);
    
    $stats = UserGamificationStat::where('user_id', $user->id)->first();
    expect($stats->current_streak)->toBe(3);
});

test('reset streak untuk user tanpa last activity date', function () {
    $user = User::factory()->create();
    
    UserGamificationStat::factory()->create([
        'user_id' => $user->id,
        'current_streak' => 2,
        'last_activity_date' => null,
    ]);

    $resetCount = $this->service->resetInactiveStreaks();

    expect($resetCount)->toBe(1);
    
    $stats = UserGamificationStat::where('user_id', $user->id)->first();
    expect($stats->current_streak)->toBe(0);
});

test('tidak reset streak yang sudah 0', function () {
    $user = User::factory()->create();
    
    UserGamificationStat::factory()->create([
        'user_id' => $user->id,
        'current_streak' => 0,
        'last_activity_date' => Carbon::yesterday()->subDays(3),
    ]);

    $resetCount = $this->service->resetInactiveStreaks();

    expect($resetCount)->toBe(0);
});
