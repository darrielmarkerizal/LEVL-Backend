<?php

namespace Modules\Content\Http\Controllers;

use App\Contracts\Services\ContentServiceInterface;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use App\Support\Traits\HandlesFiltering;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Content\Http\Requests\CreateAnnouncementRequest;
use Modules\Content\Http\Requests\ScheduleContentRequest;
use Modules\Content\Http\Requests\UpdateContentRequest;
use Modules\Content\Http\Resources\AnnouncementResource;
use Modules\Content\Models\Announcement;


class AnnouncementController extends Controller
{
    use ApiResponse;
    use HandlesFiltering;

    protected ContentServiceInterface $contentService;

    public function __construct(ContentServiceInterface $contentService)
    {
        $this->contentService = $contentService;
    }

    
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        $params = $this->extractFilterParams($request);

        
        if ($request->has('filter.unread')) {
            $params['filter']['unread'] = $request->boolean('filter.unread');
        }

        $filters = array_merge($params['filter'], [
            'per_page' => $params['per_page'],
            'sort' => $params['sort'] ?? null,
        ]);

        $announcements = $this->contentService->getAnnouncementsForUser($user, $filters);

        return $this->paginateResponse($announcements);
    }

    
    public function store(CreateAnnouncementRequest $request): JsonResponse
    {
        $this->authorize('create', Announcement::class);

        $announcement = $this->contentService->createAnnouncement(
            $request->validated(),
            auth()->user(),
        );

        
        if ($request->input('status') === 'published') {
            $this->contentService->publishContent($announcement);
        }

        
        if ($request->filled('scheduled_at')) {
            $this->contentService->scheduleContent(
                $announcement,
                \Carbon\Carbon::parse($request->input('scheduled_at')),
            );
        }

        return $this->created(
            ['announcement' => AnnouncementResource::make($announcement)],
            __('messages.announcements.created'),
        );
    }

    
    public function show(int $id): JsonResponse
    {
        $announcement = Announcement::with(['author', 'course', 'revisions.editor'])->findOrFail($id);

        $this->authorize('view', $announcement);

        
        $this->contentService->markAsRead($announcement, auth()->user());

        
        $this->contentService->incrementViews($announcement);

        return $this->success(['announcement' => $announcement]);
    }

    
    public function update(UpdateContentRequest $request, int $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);

        $this->authorize('update', $announcement);

        $announcement = $this->contentService->updateAnnouncement(
            $announcement,
            $request->validated(),
            auth()->user(),
        );

        return $this->success(
            ['announcement' => AnnouncementResource::make($announcement)],
            __('messages.announcements.updated'),
        );
    }

    
    public function destroy(int $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);

        $this->authorize('delete', $announcement);

        $this->contentService->deleteContent($announcement, auth()->user());

        return $this->success([], __('messages.announcements.deleted'));
    }

    
    public function publish(int $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);

        $this->authorize('publish', $announcement);

        $this->contentService->publishContent($announcement);

        return $this->success(
            ['announcement' => $announcement->fresh()],
            __('messages.announcements.published'),
        );
    }

    
    public function schedule(ScheduleContentRequest $request, int $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);

        $this->authorize('schedule', $announcement);

        $this->contentService->scheduleContent(
            $announcement,
            \Carbon\Carbon::parse($request->input('scheduled_at')),
        );

        return $this->success(
            ['announcement' => $announcement->fresh()],
            __('messages.announcements.scheduled'),
        );
    }

    
    public function markAsRead(int $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);

        $this->contentService->markAsRead($announcement, auth()->user());

        return $this->success([], __('messages.announcements.marked_read'));
    }
}
