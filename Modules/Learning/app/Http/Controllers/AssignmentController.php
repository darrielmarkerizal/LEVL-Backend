<?php

namespace Modules\Learning\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Services\AssignmentService;

class AssignmentController extends Controller
{
    use ApiResponse;

    public function __construct(private AssignmentService $service) {}

    public function index(Request $request, \Modules\Schemes\Models\Course $course, \Modules\Schemes\Models\Unit $unit, \Modules\Schemes\Models\Lesson $lesson)
    {
        $query = Assignment::query()
            ->where('lesson_id', $lesson->id)
            ->with(['creator:id,name,email', 'lesson:id,title,slug']);

        $status = $request->query('status');
        if ($status) {
            $query->where('status', $status);
        }

        $assignments = $query->orderBy('created_at', 'desc')->get();

        return $this->success(['assignments' => $assignments]);
    }

    public function store(Request $request, \Modules\Schemes\Models\Course $course, \Modules\Schemes\Models\Unit $unit, \Modules\Schemes\Models\Lesson $lesson)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'submission_type' => ['required', 'in:text,file,mixed'],
            'max_score' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'available_from' => ['nullable', 'date'],
            'deadline_at' => ['nullable', 'date', 'after_or_equal:available_from'],
            'status' => ['nullable', 'in:draft,published,archived'],
            'allow_resubmit' => ['nullable', 'boolean'],
            'late_penalty_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $validated['lesson_id'] = $lesson->id;
        /** @var \Modules\Auth\Models\User $user */
        $user = auth('api')->user();

        $assignment = $this->service->create($validated, $user->id);

        return $this->created(['assignment' => $assignment], 'Assignment berhasil dibuat.');
    }

    public function show(Assignment $assignment)
    {
        $assignment->load(['creator:id,name,email', 'lesson:id,title,slug']);

        return $this->success(['assignment' => $assignment]);
    }

    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'submission_type' => ['sometimes', 'in:text,file,mixed'],
            'max_score' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'available_from' => ['nullable', 'date'],
            'deadline_at' => ['nullable', 'date', 'after_or_equal:available_from'],
            'status' => ['sometimes', 'in:draft,published,archived'],
            'allow_resubmit' => ['nullable', 'boolean'],
            'late_penalty_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $updated = $this->service->update($assignment, $validated);

        return $this->success(['assignment' => $updated], 'Assignment berhasil diperbarui.');
    }

    public function destroy(Assignment $assignment)
    {
        $this->service->delete($assignment);

        return $this->success([], 'Assignment berhasil dihapus.');
    }

    public function publish(Assignment $assignment)
    {
        $updated = $this->service->publish($assignment);

        return $this->success(['assignment' => $updated], 'Assignment berhasil dipublish.');
    }

    public function unpublish(Assignment $assignment)
    {
        $updated = $this->service->unpublish($assignment);

        return $this->success(['assignment' => $updated], 'Assignment berhasil diunpublish.');
    }
}

