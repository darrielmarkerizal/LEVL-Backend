<?php

namespace Modules\Content\Contracts\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Auth\Models\User;
use Modules\Content\DTOs\CreateAnnouncementDTO;
use Modules\Content\DTOs\UpdateAnnouncementDTO;
use Modules\Content\Models\Announcement;

interface AnnouncementServiceInterface
{
    public function getForUser(User $user, array $filters = []): LengthAwarePaginator;

    public function getForCourse(int $courseId, array $filters = []): LengthAwarePaginator;

    public function find(int $id): ?Announcement;

    public function create(CreateAnnouncementDTO $dto, User $author, ?int $courseId = null): Announcement;

    public function update(Announcement $announcement, UpdateAnnouncementDTO $dto, User $editor): Announcement;

    public function delete(Announcement $announcement, User $user): bool;

    public function publish(Announcement $announcement): Announcement;

    public function schedule(Announcement $announcement, \Carbon\Carbon $publishAt): Announcement;

    public function getScheduledForPublishing(): Collection;

    public function getUnreadCount(User $user): int;
}
