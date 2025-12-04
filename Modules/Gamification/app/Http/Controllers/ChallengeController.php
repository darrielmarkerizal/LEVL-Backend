<?php

namespace Modules\Gamification\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Gamification\Models\Challenge;
use Modules\Gamification\Services\ChallengeService;

class ChallengeController extends Controller
{
    public function __construct(
        private readonly ChallengeService $challengeService
    ) {}

    /**
     * Get list of active challenges.
     *
     * @summary Mengambil daftar challenge aktif
     */
    public function index(Request $request): JsonResponse
    {
        $challenges = Challenge::active()
            ->with('badge')
            ->orderBy('type')
            ->orderBy('points_reward', 'desc')
            ->paginate($request->input('per_page', 15));

        $userId = $request->user()?->id;

        // Add user progress if authenticated
        if ($userId) {
            $userChallenges = $this->challengeService->getUserChallenges($userId)
                ->keyBy('challenge_id');

            $challenges->getCollection()->transform(function ($challenge) use ($userChallenges) {
                $assignment = $userChallenges->get($challenge->id);
                $challenge->user_progress = $assignment ? [
                    'current' => $assignment->current_progress,
                    'target' => $challenge->criteria_target,
                    'percentage' => $assignment->getProgressPercentage(),
                    'status' => $assignment->status->value,
                    'expires_at' => $assignment->expires_at,
                ] : null;

                return $challenge;
            });
        }

        return ApiResponse::success($challenges);
    }

    /**
     * Get challenge details.
     *
     * @summary Mengambil detail challenge
     */
    public function show(int $challengeId, Request $request): JsonResponse
    {
        $challenge = Challenge::with('badge')->find($challengeId);

        if (! $challenge) {
            return ApiResponse::error('Challenge tidak ditemukan.', 404);
        }

        $userId = $request->user()?->id;

        if ($userId) {
            $userChallenges = $this->challengeService->getUserChallenges($userId)
                ->keyBy('challenge_id');

            $assignment = $userChallenges->get($challenge->id);
            $challenge->user_progress = $assignment ? [
                'current' => $assignment->current_progress,
                'target' => $challenge->criteria_target,
                'percentage' => $assignment->getProgressPercentage(),
                'status' => $assignment->status->value,
                'expires_at' => $assignment->expires_at,
                'is_claimable' => $assignment->isClaimable(),
            ] : null;
        }

        return ApiResponse::success(['challenge' => $challenge]);
    }

    /**
     * Get challenges assigned to current user.
     *
     * @summary Mengambil challenge yang di-assign ke user
     */
    public function myChallenges(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $challenges = $this->challengeService->getUserChallenges($userId);

        $data = $challenges->map(function ($assignment) {
            return [
                'id' => $assignment->id,
                'challenge' => $assignment->challenge,
                'progress' => [
                    'current' => $assignment->current_progress,
                    'target' => $assignment->challenge?->criteria_target ?? 1,
                    'percentage' => $assignment->getProgressPercentage(),
                ],
                'status' => $assignment->status->value,
                'status_label' => $assignment->status->label(),
                'assigned_date' => $assignment->assigned_date,
                'expires_at' => $assignment->expires_at,
                'is_claimable' => $assignment->isClaimable(),
            ];
        });

        return ApiResponse::success(['challenges' => $data]);
    }

    /**
     * Get user's completed challenges.
     *
     * @summary Mengambil riwayat challenge yang sudah selesai
     */
    public function completed(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $limit = $request->input('limit', 15);

        $completions = $this->challengeService->getCompletedChallenges($userId, $limit);

        $data = $completions->map(function ($completion) {
            return [
                'id' => $completion->id,
                'challenge' => $completion->challenge,
                'completed_date' => $completion->completed_date,
                'xp_earned' => $completion->xp_earned,
                'completion_data' => $completion->completion_data,
            ];
        });

        return ApiResponse::success(['completions' => $data]);
    }

    /**
     * Claim reward for completed challenge.
     *
     * @summary Klaim reward challenge yang sudah selesai
     */
    public function claim(int $challengeId, Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        try {
            $rewards = $this->challengeService->claimReward($userId, $challengeId);

            return ApiResponse::success([
                'message' => 'Reward berhasil diklaim!',
                'rewards' => $rewards,
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}
