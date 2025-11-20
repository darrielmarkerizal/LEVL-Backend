<?php

namespace Modules\Assessments\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Assessments\Models\Question;
use Modules\Assessments\Models\Exercise;
use Modules\Assessments\Services\QuestionService;

class QuestionController extends Controller
{
    public function __construct(private readonly QuestionService $service) {}

    /**
     * Create new question in exercise
     */
    public function store(Request $request, Exercise $exercise)
    {
        $this->authorize('update', $exercise);

        $validated = $request->validate([
            'question_text' => 'required|string',
            'type' => 'required|in:multiple_choice,free_text,file_upload,true_false',
            'score_weight' => 'required|numeric|min:0.1',
            'is_required' => 'boolean',
            'order' => 'integer|min:1',
        ]);

        $question = $this->service->create($exercise, $validated);

        return response()->json(['data' => ['question' => $question]], 201);
    }

    /**
     * Get question details
     */
    public function show(Question $question)
    {
        $question = $this->service->show($question);

        return response()->json(['data' => ['question' => $question]]);
    }

    /**
     * Update question
     */
    public function update(Request $request, Question $question)
    {
        $this->authorize('update', $question->exercise);

        $validated = $request->validate([
            'question_text' => 'sometimes|string',
            'type' => 'sometimes|in:multiple_choice,free_text,file_upload,true_false',
            'score_weight' => 'sometimes|numeric|min:0.1',
            'is_required' => 'boolean',
            'order' => 'integer|min:1',
        ]);

        $question = $this->service->update($question, $validated);

        return response()->json(['data' => ['question' => $question]]);
    }

    /**
     * Delete question
     */
    public function destroy(Question $question)
    {
        $this->authorize('update', $question->exercise);

        $this->service->delete($question);

        return response()->json(null, 204);
    }
}
