<?php

namespace Modules\Assessments\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Assessments\Models\QuestionOption;
use Modules\Assessments\Models\Question;
use Modules\Assessments\Services\QuestionOptionService;

class QuestionOptionController extends Controller
{
    public function __construct(private readonly QuestionOptionService $service) {}

    /**
     * Create new option for question
     */
    public function store(Request $request, Question $question)
    {
        $this->authorize('update', $question->exercise);

        $validated = $request->validate([
            'option_text' => 'required|string',
            'is_correct' => 'boolean',
            'order' => 'integer|min:1',
        ]);

        $option = $this->service->create($question, $validated);

        return response()->json(['data' => ['option' => $option]], 201);
    }

    /**
     * Update option
     */
    public function update(Request $request, QuestionOption $option)
    {
        $this->authorize('update', $option->question->exercise);

        $validated = $request->validate([
            'option_text' => 'sometimes|string',
            'is_correct' => 'boolean',
            'order' => 'integer|min:1',
        ]);

        $option = $this->service->update($option, $validated);

        return response()->json(['data' => ['option' => $option]]);
    }

    /**
     * Delete option
     */
    public function destroy(QuestionOption $option)
    {
        $this->authorize('update', $option->question->exercise);

        $this->service->delete($option);

        return response()->json(null, 204);
    }
}
