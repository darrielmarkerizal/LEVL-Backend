<?php

namespace Modules\Forums\Contracts\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Forums\Models\Reply;

interface ReplyRepositoryInterface
{
    public function paginateByThread(int $threadId, array $filters = []): LengthAwarePaginator;

    public function find(int $id): ?Reply;

    public function create(array $data): Reply;

    public function update(Reply $reply, array $data): Reply;

    public function delete(Reply $reply): bool;

    public function markAsSolution(Reply $reply): Reply;

    public function unmarkAsSolution(Reply $reply): Reply;
}
