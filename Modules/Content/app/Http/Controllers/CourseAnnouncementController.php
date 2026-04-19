<?php

namespace Modules\Content\Http\Controllers;

use App\Contracts\Services\ContentServiceInterface;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use App\Support\Traits\HandlesFiltering;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Content\Http\Requests\CreateAnnouncementRequest;
use Modules\Content\Models\Announcement;
use Modules\Content\Repositories\AnnouncementRepository;
use Modules\Schemes\Models\Course;


class CourseAnnouncementController extends Controller
{
    use ApiResponse;
    use HandlesFiltering;

    protected ContentServiceInterface $contentService;

    protected AnnouncementRepository $announcementRepository;

    public function __construct(
        ContentServiceInterface $contentService,
        AnnouncementRepository $announcementRepository
    ) {
        $this->contentService = $contentService;
        $this->announcementRepository = $announcementRepository;
    }

    
    public function index(Request $request, int $courseId): JsonResponse
    {
        $course = Course::findOrFail($courseId);

        $params = $this->extractFilterParams($request);

        $announcements = $this->announcementRepository->getAnnouncementsForCourse($courseId, $params);

        return $this->success($announcements);
    }

    
    public function store(CreateAnnouncementRequest $request, int $courseId): JsonResponse
    {
        $course = Course::findOrFail($courseId);

        $this->authorize('createCourseAnnouncement', [Announcement::class, $courseId]);

        $data = $request->validated();
        $data['course_id'] = $courseId;
        $data['target_type'] = 'course';

        $announcement = $this->contentService->createAnnouncement($data, auth()->user());

        
        if ($request->input('status') === 'published') {
            $this->contentService->publishContent($announcement);
        }

        
        if ($request->filled('scheduled_at')) {
            $this->contentService->scheduleContent(
                $announcement,
                \Carbon\Carbon::parse($request->input('scheduled_at'))
            );
        }

        return $this->created(
            $announcement,
            'Pengumuman kursus berhasil dibuat.'
        );
    }
}
