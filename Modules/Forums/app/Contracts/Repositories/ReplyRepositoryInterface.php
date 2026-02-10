<?php

declare(strict_types=1);

namespace Modules\Forums\Contracts\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Forums\Models\Reply;

interface ReplyRepositoryInterface
{
    public function create(array $data): Reply;

    public function update(Reply $reply, array $data): Reply;

    public function delete(Reply $reply, ?int $deletedBy = null): bool;

    public function findWithRelations(int $replyId): ?Reply;

    public function paginateTopLevelReplies(int $threadId, int $perPage, int $page): LengthAwarePaginator;

    public function getAcceptedAnswer(int $threadId): ?Reply;

    public function markAsAccepted(Reply $reply): bool;

    public function unmarkAsAccepted(Reply $reply): bool;
}
