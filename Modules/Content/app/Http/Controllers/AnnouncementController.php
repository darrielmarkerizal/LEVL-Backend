<?php

namespace Modules\Content\Http\Controllers;

use App\Contracts\Services\ContentServiceInterface;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Content\Http\Requests\CreateAnnouncementRequest;
use Modules\Content\Http\Requests\ScheduleContentRequest;
use Modules\Content\Http\Requests\UpdateContentRequest;
use Modules\Content\Models\Announcement;

/**
 * @tags Konten & Berita
 */
class AnnouncementController extends Controller
{
    use ApiResponse;

    protected ContentServiceInterface $contentService;

    public function __construct(ContentServiceInterface $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * @summary Daftar Pengumuman
     *
     * @allowedFilters course_id, priority, unread
     *
     * @allowedSorts created_at, published_at
     *
     * @filterEnum priority low|medium|high
     * @filterEnum unread true|false
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        $filters = [
            'course_id' => $request->input('course_id'),
            'priority' => $request->input('priority'),
            'unread' => $request->boolean('unread'),
            'per_page' => $request->input('per_page', 15),
        ];

        $announcements = $this->contentService->getAnnouncementsForUser($user, $filters);

        return $this->paginateResponse($announcements);
    }

    /**
     * @summary Buat Pengumuman Baru
     */
    public function store(CreateAnnouncementRequest $request): JsonResponse
    {
        $this->authorize('createAnnouncement', Announcement::class);

        try {
            $announcement = $this->contentService->createAnnouncement(
                $request->validated(),
                auth()->user()
            );

            // Auto-publish if status is published
            if ($request->input('status') === 'published') {
                $this->contentService->publishContent($announcement);
            }

            // Auto-schedule if scheduled_at is provided
            if ($request->filled('scheduled_at')) {
                $this->contentService->scheduleContent(
                    $announcement,
                    \Carbon\Carbon::parse($request->input('scheduled_at'))
                );
            }

            return $this->created(
                ['announcement' => $announcement->load(['author', 'course'])],
                'Pengumuman berhasil dibuat.'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * @summary Detail Pengumuman
     */
    public function show(int $id): JsonResponse
    {
        $announcement = Announcement::with(['author', 'course', 'revisions.editor'])
            ->findOrFail($id);

        $this->authorize('view', $announcement);

        // Mark as read by current user
        $this->contentService->markAsRead($announcement, auth()->user());

        // Increment views
        $this->contentService->incrementViews($announcement);

        return $this->success(['announcement' => $announcement]);
    }

    /**
     * @summary Perbarui Pengumuman
     */
    public function update(UpdateContentRequest $request, int $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);

        $this->authorize('update', $announcement);

        try {
            $announcement = $this->contentService->updateAnnouncement(
                $announcement,
                $request->validated(),
                auth()->user()
            );

            return $this->success(
                ['announcement' => $announcement->load(['author', 'course'])],
                'Pengumuman berhasil diperbarui.'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * @summary Hapus Pengumuman
     */
    public function destroy(int $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);

        $this->authorize('delete', $announcement);

        $this->contentService->deleteContent($announcement, auth()->user());

        return $this->success([], 'Pengumuman berhasil dihapus.');
    }

    /**
     * @summary Publikasikan Pengumuman
     */
    public function publish(int $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);

        $this->authorize('publish', $announcement);

        $this->contentService->publishContent($announcement);

        return $this->success(
            ['announcement' => $announcement->fresh()],
            'Pengumuman berhasil dipublikasikan.'
        );
    }

    /**
     * @summary Jadwalkan Pengumuman
     */
    public function schedule(ScheduleContentRequest $request, int $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);

        $this->authorize('schedule', $announcement);

        try {
            $this->contentService->scheduleContent(
                $announcement,
                \Carbon\Carbon::parse($request->input('scheduled_at'))
            );

            return $this->success(
                ['announcement' => $announcement->fresh()],
                'Pengumuman berhasil dijadwalkan.'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * @summary Tandai Pengumuman Dibaca
     */
    public function markAsRead(int $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);

        $this->contentService->markAsRead($announcement, auth()->user());

        return $this->success([], 'Pengumuman ditandai sudah dibaca.');
    }
}
