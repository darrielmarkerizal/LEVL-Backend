<?php

declare(strict_types=1);

namespace Modules\Gamification\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
    /**
     * Get gamification system metrics for monitoring
     * Useful for Prometheus/Grafana integration
     */
    public function index(): JsonResponse
    {
        $metrics = Cache::remember('gamification.metrics', 60, function () {
            return [
                // Badge metrics
                'badge_evaluations_total' => $this->getBadgeEvaluationsTotal(),
                'badge_awarded_total' => $this->getBadgeAwardedTotal(),
                'badge_awarded_last_hour' => $this->getBadgeAwardedLastHour(),

                // Counter metrics
                'counter_increment_total' => $this->getCounterIncrementTotal(),
                'active_counters' => $this->getActiveCounters(),

                // Event log metrics
                'event_logs_total' => $this->getEventLogsTotal(),
                'event_logs_last_hour' => $this->getEventLogsLastHour(),

                // Performance metrics
                'rule_eval_duration_ms' => $this->getRuleEvalDuration(),
                'cache_hit_rate' => $this->getCacheHitRate(),

                // System health
                'cooldowns_active' => $this->getActiveCooldowns(),
                'badge_versions_active' => $this->getActiveBadgeVersions(),

                // Timestamp
                'collected_at' => now()->toIso8601String(),
            ];
        });

        return response()->json($metrics);
    }

    private function getBadgeEvaluationsTotal(): int
    {
        // This would be tracked via Redis counter in production
        // For now, estimate from event logs
        return DB::table('gamification_event_logs')
            ->whereIn('event_type', ['lesson_completed', 'assignment_submitted', 'login'])
            ->count();
    }

    private function getBadgeAwardedTotal(): int
    {
        return DB::table('user_badges')->count();
    }

    private function getBadgeAwardedLastHour(): int
    {
        return DB::table('user_badges')
            ->where('created_at', '>=', now()->subHour())
            ->count();
    }

    private function getCounterIncrementTotal(): int
    {
        return DB::table('user_event_counters')->sum('counter');
    }

    private function getActiveCounters(): int
    {
        return DB::table('user_event_counters')
            ->where('counter', '>', 0)
            ->count();
    }

    private function getEventLogsTotal(): int
    {
        return DB::table('gamification_event_logs')->count();
    }

    private function getEventLogsLastHour(): int
    {
        return DB::table('gamification_event_logs')
            ->where('created_at', '>=', now()->subHour())
            ->count();
    }

    private function getRuleEvalDuration(): float
    {
        // This would be tracked via APM in production
        // For now, return cached value or 0
        return Cache::get('gamification.rule_eval_duration_ms', 0.0);
    }

    private function getCacheHitRate(): float
    {
        // This would be tracked via Redis stats in production
        // For now, return estimated value
        return 0.95; // 95% cache hit rate (estimated)
    }

    private function getActiveCooldowns(): int
    {
        return DB::table('badge_rule_cooldowns')
            ->where('can_evaluate_after', '>', now())
            ->count();
    }

    private function getActiveBadgeVersions(): int
    {
        return DB::table('badge_versions')
            ->where('is_active', true)
            ->count();
    }
}
