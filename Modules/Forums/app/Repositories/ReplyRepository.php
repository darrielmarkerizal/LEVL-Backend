<?php

declare(strict_types=1);

namespace Modules\Forums\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Forums\Contracts\Repositories\ReplyRepositoryInterface;
use Modules\Forums\Models\Reply;

class ReplyRepository extends BaseRepository implements ReplyRepositoryInterface
{
    protected function model(): string
    {
        return Reply::class;
    }

    protected array $allowedFilters = ['thread_id', 'parent_id', 'is_accepted_answer'];

    protected array $allowedSorts = ['id', 'created_at', 'is_accepted_answer'];

    protected string $defaultSort = 'created_at';

    protected array $with = ['author.media', 'mentions.user.media'];

    public function getRepliesForThread(int $threadId): Collection
    {
        return Reply::where('thread_id', $threadId)
            ->with(['author', 'mentions.user.media', 'children.author', 'children.mentions.user.media', 'children.children.author'])
            ->withCount('reactions')
            ->orderBy('is_accepted_answer', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getTopLevelReplies(int $threadId): Collection
    {
        return Reply::where('thread_id', $threadId)
            ->topLevel()
            ->with(['author.media', 'mentions.user.media', 'children.author.media', 'children.mentions.user.media', 'media', 'children.media', 'reactions', 'children.reactions'])
            ->withCount('reactions')
            ->orderByRaw('CASE WHEN is_accepted_answer = true THEN 1 WHEN EXISTS (SELECT 1 FROM replies as r2 WHERE r2.parent_id = replies.id AND r2.is_accepted_answer = true) THEN 1 ELSE 0 END DESC')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function paginateTopLevelReplies(int $threadId, int $perPage, int $page): LengthAwarePaginator
    {
        return cache()->tags(['forums', 'replies', "thread:{$threadId}"])->remember(
            "forums:replies:thread:{$threadId}:top:{$perPage}:{$page}",
            300,
            function () use ($threadId, $perPage, $page) {
                return Reply::where('thread_id', $threadId)
                    ->topLevel()
                    ->with([
                        'author.media',
                        'mentions.user.media',
                        'media',
                        'reactions',
                        'children' => function ($query) {
                            $query->with(['author.media', 'mentions.user.media', 'media', 'reactions'])
                                ->withCount('children');
                        },
                    ])
                    ->withCount(['reactions', 'children'])
                    ->orderByRaw('CASE WHEN is_accepted_answer = true THEN 1 WHEN EXISTS (SELECT 1 FROM replies as r2 WHERE r2.parent_id = replies.id AND r2.is_accepted_answer = true) THEN 1 ELSE 0 END DESC')
                    ->orderBy('created_at', 'asc')
                    ->paginate($perPage, ['*'], 'page', $page);
            }
        );
    }

    public function getNestedReplies(int $parentId): Collection
    {
        return Reply::where('parent_id', $parentId)
            ->with(['author.media', 'mentions.user.media', 'children.author.media', 'children.mentions.user.media', 'media', 'children.media'])
            ->withCount('reactions')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function create(array $data): Reply
    {
        return Reply::create($data);
    }

    public function update(Model $model, array $attributes): Reply
    {
        $model->update($attributes);

        
        return $model->fresh();
    }

    public function delete(Model $model, ?int $deletedBy = null): bool
    {
        if ($deletedBy && $model instanceof Reply) {
            $model->deleted_by = $deletedBy;
            $model->save();
        }

        return $model->delete();
    }

    public function findWithRelations(int $replyId): ?Reply
    {
        return Reply::with(['author.media', 'mentions.user.media', 'thread', 'parent', 'children', 'media', 'children.media', 'children.author.media', 'children.mentions.user.media', 'reactions', 'children.reactions'])
            ->withCount(['reactions', 'children'])
            ->find($replyId);
    }

    public function getChildrenOf(int $parentId): Collection
    {
        return Reply::where('parent_id', $parentId)
            ->with(['author.media', 'mentions.user.media', 'media', 'reactions'])
            ->withCount(['reactions', 'children'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getAcceptedAnswer(int $threadId): ?Reply
    {
        return Reply::where('thread_id', $threadId)
            ->accepted()
            ->with(['author.media', 'mentions.user.media'])
            ->first();
    }

    public function markAsAccepted(Reply $reply): bool
    {
        Reply::where('thread_id', $reply->thread_id)
            ->where('is_accepted_answer', true)
            ->update(['is_accepted_answer' => false]);

        $reply->is_accepted_answer = true;

        return $reply->save();
    }

    public function unmarkAsAccepted(Reply $reply): bool
    {
        $reply->is_accepted_answer = false;

        return $reply->save();
    }
}
