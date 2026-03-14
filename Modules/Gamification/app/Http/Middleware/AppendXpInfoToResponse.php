<?php

declare(strict_types=1);

namespace Modules\Gamification\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Gamification\Models\Point;
use Modules\Gamification\Models\UserGamificationStat;

class AppendXpInfoToResponse
{
    /**
     * Handle an incoming request and append XP info to response
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only process JSON responses for authenticated users
        if (!$response instanceof JsonResponse || !auth()->check()) {
            return $response;
        }

        $userId = auth()->id();
        $data = $response->getData(true);

        // Check if response already has xp_info
        if (isset($data['xp_info'])) {
            return $response;
        }

        // Get user's gamification stats
        $stats = UserGamificationStat::where('user_id', $userId)->first();

        if (!$stats) {
            return $response;
        }

        // Get latest XP award (if any in last 5 seconds)
        $latestXpAward = Point::where('user_id', $userId)
            ->where('created_at', '>=', now()->subSeconds(5))
            ->latest()
            ->first();

        // Append XP info to response
        $xpInfo = [
            'current_xp' => $stats->total_xp,
            'current_level' => $stats->global_level,
        ];

        if ($latestXpAward) {
            $xpInfo['latest_xp_award'] = [
                'xp_awarded' => $latestXpAward->points,
                'reason' => $latestXpAward->reason,
                'description' => $latestXpAward->description,
                'xp_source_code' => $latestXpAward->xp_source_code,
                'leveled_up' => $latestXpAward->triggered_level_up,
                'old_level' => $latestXpAward->old_level,
                'new_level' => $latestXpAward->new_level,
                'awarded_at' => $latestXpAward->created_at->toIso8601String(),
            ];
        }

        $data['gamification'] = $xpInfo;

        return $response->setData($data);
    }
}
