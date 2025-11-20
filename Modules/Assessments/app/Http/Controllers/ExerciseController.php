<?php

namespace Modules\Assessments\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Assessments\Models\Exercise;
use Modules\Assessments\Services\ExerciseService;

class ExerciseController extends Controller
{
    public function __construct(private readonly ExerciseService $service) {}

    /**
     * List all exercises with filtering
     */
    public function index(Request $request)
    {
        $perPage = max(1, (int) $request->get('per_page', 15));
        $exercises = $this->service->paginate($request->all(), $perPage);

        return response()->json(['data' => $exercises]);
    }

    /**
     * Create new exercise
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $this->authorize('create', Exercise::class);

        $validated = $request->validate([
            'scope_type' => 'required|in:course,unit,lesson',
            'scope_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:quiz,exam,homework',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'max_score' => 'required|numeric|min:0',
            'available_from' => 'nullable|date_time',
            'available_until' => 'nullable|date_time',
        ]);

        $exercise = $this->service->create($validated, $user->id);

        return response()->json(['data' => ['exercise' => $exercise]], 201);
    }

    /**
     * Get exercise details
     */
    public function show(Exercise $exercise)
    {
        return response()->json(['data' => ['exercise' => $exercise]]);
    }

    /**
     * Update exercise
     */
    public function update(Request $request, Exercise $exercise)
    {
        $this->authorize('update', $exercise);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:quiz,exam,homework',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'max_score' => 'sometimes|numeric|min:0',
            'available_from' => 'nullable|date_time',
            'available_until' => 'nullable|date_time',
        ]);

        $updated = $this->service->update($exercise, $validated);

        return response()->json(['data' => ['exercise' => $updated]]);
    }

    /**
     * Delete exercise
     */
    public function destroy(Exercise $exercise)
    {
        $this->authorize('delete', $exercise);

        $this->service->delete($exercise);

        return response()->json(null, 204);
    }

    /**
     * Publish exercise
     */
    public function publish(Exercise $exercise)
    {
        $this->authorize('update', $exercise);

        $published = $this->service->publish($exercise);

        return response()->json(['data' => ['exercise' => $published]]);
    }

    /**
     * Get exercise questions
     */
    public function getQuestions(Exercise $exercise)
    {
        $questions = $this->service->questions($exercise);

        return response()->json(['data' => ['questions' => $questions]]);
    }
}
