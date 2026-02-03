<?php

namespace Modules\Forums\Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Models\User;
use Modules\Forums\Models\ForumStatistic;
use Modules\Schemes\Models\Course;

class ForumStatisticFactory extends Factory
{
     
    protected $model = ForumStatistic::class;

     
    public function definition(): array
    {
        
        $month = $this->faker->unique()->numberBetween(1, 12);
        $year = $this->faker->numberBetween(2020, 2025);
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = Carbon::create($year, $month, 1)->endOfMonth();

        return [
            'scheme_id' => Course::factory(),
            'user_id' => null,
            'threads_count' => $this->faker->numberBetween(0, 100),
            'replies_count' => $this->faker->numberBetween(0, 500),
            'views_count' => $this->faker->numberBetween(0, 1000),
            'avg_response_time_minutes' => $this->faker->numberBetween(10, 120),
            'response_rate' => $this->faker->randomFloat(2, 0, 100),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
        ];
    }

     
    public function forUser(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user?->id ?? User::factory(),
        ]);
    }
}
