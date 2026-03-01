<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Models\Quiz;
use Modules\Schemes\Models\Course;

class AssessmentController extends Controller
{
    use ApiResponse;
    use AuthorizesRequests;

    public function index(Course $course): JsonResponse
    {
        $this->authorize('viewAny', Assignment::class);

        $assignments = Assignment::forCourse($course->id)->get()->map(fn($item) => [
            'id' => $item->id,
            'type' => 'assignment',
            'title' => $item->title,
            'description' => $item->description,
            'status' => $item->status->value,
            'max_score' => $item->max_score,
            'created_at' => $item->created_at,
        ]);

        $quizzes = Quiz::forCourse($course->id)->get()->map(fn($item) => [
            'id' => $item->id,
            'type' => 'quiz',
            'title' => $item->title,
            'description' => $item->description,
            'status' => $item->status->value,
            'max_score' => $item->max_score,
            'created_at' => $item->created_at,
        ]);

        $assessments = $assignments->concat($quizzes)->sortByDesc('created_at')->values();

        return $this->success($assessments);
    }
}
