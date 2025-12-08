<?php

namespace Modules\Content\Contracts\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Auth\Models\User;
use Modules\Content\DTOs\CreateNewsDTO;
use Modules\Content\DTOs\UpdateNewsDTO;
use Modules\Content\Models\News;

interface NewsServiceInterface
{
    public function getFeed(array $filters = []): LengthAwarePaginator;

    public function search(string $query, array $filters = []): LengthAwarePaginator;

    public function findBySlug(string $slug): ?News;

    public function find(int $id): ?News;

    public function create(CreateNewsDTO $dto, User $author): News;

    public function update(News $news, UpdateNewsDTO $dto, User $editor): News;

    public function delete(News $news, User $user): bool;

    public function publish(News $news): News;

    public function schedule(News $news, \Carbon\Carbon $publishAt): News;

    public function getTrending(int $limit = 10): Collection;

    public function getFeatured(int $limit = 5): Collection;

    public function getScheduledForPublishing(): Collection;

    public function incrementViews(News $news): void;
}
