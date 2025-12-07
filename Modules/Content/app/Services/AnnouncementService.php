<?php

namespace Modules\Content\Services;

use App\Exceptions\BusinessException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Content\Contracts\Repositories\AnnouncementRepositoryInterface;
use Modules\Content\DTOs\CreateAnnouncementDTO;
use Modules\Content\DTOs\UpdateAnnouncementDTO;
use Modules\Content\Events\AnnouncementPublished;
use Modules\Content\Models\Announcement;
use Modules\Content\Models\ContentRevision;

class AnnouncementService
{
    public function __construct(
        private AnnouncementRepositoryInterface $repository
    ) {}

    public function getForUser(User $user, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getAnnouncementsForUser($user, $filters);
    }

    public function getForCourse(int $courseId, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getAnnouncementsForCourse($courseId, $filters);
    }

    public function find(int $id): ?Announcement
    {
        return $this->repository->findWithRelations($id);
    }

    public function create(CreateAnnouncementDTO $dto, User $author, ?int $courseId = null): Announcement
    {
        return DB::transaction(function () use ($dto, $author, $courseId) {
            $data = array_merge($dto->toArrayWithoutNull(), [
                'author_id' => $author->id,
                'course_id' => $courseId,
                'target_type' => 'all',
                'priority' => $dto->priority ?? 'normal',
            ]);

            return $this->repository->create($data);
        });
    }

    public function update(Announcement $announcement, UpdateAnnouncementDTO $dto, User $editor): Announcement
    {
        return DB::transaction(function () use ($announcement, $dto, $editor) {
            $this->saveRevision($announcement, $editor);

            return $this->repository->update($announcement, $dto->toArrayWithoutNull());
        });
    }

    public function delete(Announcement $announcement, User $user): bool
    {
        return $this->repository->delete($announcement, $user->id);
    }

    /**
     * @throws BusinessException
     */
    public function publish(Announcement $announcement): Announcement
    {
        if ($announcement->status === 'published') {
            throw new BusinessException('Announcement sudah dipublikasikan.');
        }

        return DB::transaction(function () use ($announcement) {
            $this->repository->update($announcement, [
                'status' => 'published',
                'published_at' => now(),
                'scheduled_at' => null,
            ]);

            event(new AnnouncementPublished($announcement->fresh()));

            return $announcement->fresh();
        });
    }

    /**
     * @throws BusinessException
     */
    public function schedule(Announcement $announcement, \Carbon\Carbon $publishAt): Announcement
    {
        if ($publishAt->isPast()) {
            throw new BusinessException('Waktu jadwal harus di masa depan.');
        }

        $this->repository->update($announcement, [
            'status' => 'scheduled',
            'scheduled_at' => $publishAt,
        ]);

        return $announcement->fresh();
    }

    public function getScheduledForPublishing(): Collection
    {
        return $this->repository->getScheduledForPublishing();
    }

    public function getUnreadCount(User $user): int
    {
        return $this->repository->getUnreadCount($user);
    }

    private function saveRevision(Announcement $announcement, User $editor): void
    {
        ContentRevision::create([
            'content_type' => Announcement::class,
            'content_id' => $announcement->id,
            'editor_id' => $editor->id,
            'title' => $announcement->title,
            'content' => $announcement->content,
        ]);
    }
}
