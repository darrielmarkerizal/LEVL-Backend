<?php

declare(strict_types=1);

namespace Modules\Learning\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Learning\Contracts\Services\AssignmentServiceInterface;
use Modules\Learning\Http\Requests\DuplicateAssignmentRequest;
use Modules\Learning\Http\Requests\StoreAssignmentRequest;
use Modules\Learning\Http\Requests\UpdateAssignmentRequest;
use Modules\Learning\Http\Resources\AssignmentResource;
use Modules\Learning\Models\Assignment;
use Modules\Learning\Services\Support\AssignmentEnrichmentService;

class AssignmentController extends Controller
{
    use ApiResponse;
    use AuthorizesRequests;

    public function __construct(
        private readonly AssignmentServiceInterface $assignmentService,
        private readonly AssignmentEnrichmentService $enrichmentService
    ) {}

    public function index(Request $request, \Modules\Schemes\Models\Course $course): JsonResponse
    {
        $user = auth('api')->user();
        $paginator = $this->assignmentService->listForIndex($course, $request->all());

        if ($user && $user->hasRole('Student')) {
            $paginator = $this->enrichmentService->enrichForStudent($paginator, $user->id);
        } else {
            $paginator = $this->enrichmentService->enrichForInstructor($paginator);
        }

        return $this->paginateResponse($paginator, 'messages.assignments.list_retrieved');
    }

    public function indexIncomplete(Request $request, \Modules\Schemes\Models\Course $course): JsonResponse
    {
        $user = auth('api')->user();
        $paginator = $this->assignmentService->listIncomplete($course, $user->id, $request->all());
        $paginator = $this->enrichmentService->enrichForStudent($paginator, $user->id);

        return $this->paginateResponse($paginator, 'messages.assignments.incomplete_list_retrieved');
    }

    public function store(StoreAssignmentRequest $request): JsonResponse
    {
        $course = $this->assignmentService->resolveCourseFromScopeOrFail($request->getResolvedScope());
        $this->authorize('create', [Assignment::class, $course]);

        $assignment = $this->assignmentService->create($request->validated(), auth('api')->id());

        return $this->created(AssignmentResource::make($assignment), __('messages.assignments.created'));
    }

    public function show(Assignment $assignment): JsonResponse
    {
        $user = auth('api')->user();

        if ($user && $user->hasRole('Student')) {
            $enriched = $this->enrichmentService->enrichSingleForStudent($assignment, $user->id);

            if ($enriched['is_locked']) {
                return $this->error(__('messages.assignments.locked'), [], 403);
            }

            return $this->success($enriched);
        }

        return $this->success(AssignmentResource::make($this->assignmentService->getWithRelations($assignment)));
    }

    public function update(UpdateAssignmentRequest $request, Assignment $assignment): JsonResponse
    {
        $updated = $this->assignmentService->update($assignment, $request->validated());

        return $this->success(AssignmentResource::make($updated), __('messages.assignments.updated'));
    }

    public function destroy(Assignment $assignment): JsonResponse
    {
        $this->assignmentService->delete($assignment);

        return $this->success([], __('messages.assignments.deleted'));
    }

    public function publish(Assignment $assignment): JsonResponse
    {
        $updated = $this->assignmentService->publish($assignment);

        return $this->success(AssignmentResource::make($updated), __('messages.assignments.published'));
    }

    public function unpublish(Assignment $assignment): JsonResponse
    {
        $updated = $this->assignmentService->unpublish($assignment);

        return $this->success(AssignmentResource::make($updated), __('messages.assignments.unpublished'));
    }

    public function archive(Assignment $assignment): JsonResponse
    {
        $archived = $this->assignmentService->archive($assignment);

        return $this->success(AssignmentResource::make($archived), __('messages.assignments.archived'));
    }

    public function checkPrerequisites(Assignment $assignment): JsonResponse
    {
        $result = $this->assignmentService->checkPrerequisites($assignment->id, auth('api')->id());
        $data = $result->toArray();

        return $this->success($data, $data['message'] ?? __('messages.success'));
    }

    public function duplicate(DuplicateAssignmentRequest $request, Assignment $assignment): JsonResponse
    {
        $this->authorize('duplicate', $assignment);
        $duplicated = $this->assignmentService->duplicateAssignment($assignment->id, auth('api')->id(), $request->validated());

        return $this->created(AssignmentResource::make($duplicated), __('messages.assignments.duplicated'));
    }
}
