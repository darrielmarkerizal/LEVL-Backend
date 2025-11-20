<?php

namespace Modules\Assessments\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Assessments\Models\Exercise;
use Modules\Assessments\Models\Attempt;
use Modules\Assessments\Models\Answer;
use Modules\Assessments\Services\GradingService;

class GradingController extends Controller
{
    public function __construct(private readonly GradingService $service) {}

    /**
     * Get all attempts for an exercise
     */
    public function getExerciseAttempts(Request $request, Exercise $exercise)
    {
        $this->authorize('view', $exercise);

        $attempts = $this->service->attempts($exercise, $request->all(), (int) $request->get('per_page', 15));

        return response()->json(['data' => $attempts]);
    }

    /**
     * Get all answers for an attempt
     */
    public function getAttemptAnswers(Attempt $attempt)
    {
        $this->authorize('view', $attempt);

        $answers = $this->service->answers($attempt);

        return response()->json(['data' => ['answers' => $answers]]);
    }

    /**
     * Add feedback to an answer (for essay/short answer)
     */
    public function addFeedback(Request $request, Answer $answer)
    {
        $this->authorize('view', $answer->attempt);

        $validated = $request->validate([
            'feedback' => 'required|string',
            'score_awarded' => 'required|numeric|min:0',
        ]);

        $answer = $this->service->addFeedback($answer, $validated);

        return response()->json(['data' => ['answer' => $answer]]);
    }

    /**
     * Update attempt's final score and feedback
     */
    public function updateAttemptScore(Request $request, Attempt $attempt)
    {
        $this->authorize('view', $attempt);

        $validated = $request->validate([
            'score' => 'required|numeric|min:0',
            'feedback' => 'nullable|string',
        ]);

        $attempt = $this->service->updateAttemptScore($attempt, $validated);

        return response()->json(['data' => ['attempt' => $attempt]]);
    }
}
