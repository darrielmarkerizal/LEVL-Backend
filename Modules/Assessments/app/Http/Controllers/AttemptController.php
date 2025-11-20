<?php

namespace Modules\Assessments\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Assessments\Models\Attempt;
use Modules\Assessments\Models\Exercise;
use Modules\Assessments\Services\AttemptService;

class AttemptController extends Controller
{
    public function __construct(private readonly AttemptService $service) {}

    /**
     * List user's attempts
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $attempts = $this->service->paginate($user, $request->all(), (int) $request->get('per_page', 15));

        return response()->json(['data' => $attempts]);
    }

    /**
     * Start new attempt
     */
    public function store(Request $request, Exercise $exercise)
    {
        $user = $request->user();

        $attempt = $this->service->start($user, $exercise);

        return response()->json(['data' => ['attempt' => $attempt]], 201);
    }

    /**
     * Get attempt details with questions
     */
    public function show(Attempt $attempt)
    {
        $this->authorize('view', $attempt);

        $attempt = $this->service->show($attempt);

        return response()->json(['data' => ['attempt' => $attempt]]);
    }

    /**
     * Submit answer for a question
     */
    public function submitAnswer(Request $request, Attempt $attempt)
    {
        $this->authorize('view', $attempt);

        $validated = $request->validate([
            'question_id' => 'required|integer|exists:questions,id',
            'selected_option_id' => 'nullable|integer|exists:question_options,id',
            'answer_text' => 'nullable|string',
        ]);

        $answer = $this->service->submitAnswer($attempt, $validated);

        return response()->json(['data' => ['answer' => $answer]]);
    }

    /**
     * Complete attempt and trigger grading
     */
    public function complete(Request $request, Attempt $attempt)
    {
        $this->authorize('view', $attempt);

        $attempt = $this->service->complete($attempt);

        return response()->json(['data' => ['attempt' => $attempt]]);
    }
}
