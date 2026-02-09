<?php

declare(strict_types=1);

namespace Modules\Forums\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Forums\Http\Resources\ThreadResource;
use Modules\Forums\Models\Thread;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class ThreadReadService
{
    public function paginateCourseThreads(int $courseId, ?string $search, int $perPage): LengthAwarePaginator
    {
        $includes = array_merge(
            ['author', 'course', 'media', 'tags'],
            $this->getRecursiveReplyIncludes()
        );

        $threadsQuery = QueryBuilder::for(Thread::class)
            ->where('course_id', $courseId)
            ->allowedFilters([
                'title',
                'content',
                AllowedFilter::exact('is_pinned'),
                AllowedFilter::exact('is_closed'),
                AllowedFilter::exact('is_resolved'),
                AllowedFilter::exact('author_id'),
                AllowedFilter::scope('is_mentioned'),
                AllowedFilter::scope('pinned'),
                AllowedFilter::scope('closed'),
                AllowedFilter::scope('resolved'),
            ])
            ->allowedSorts([
                'created_at',
                'updated_at',
                'views_count',
                'replies_count',
                'last_activity_at',
                'title',
                AllowedSort::field('pinned', 'is_pinned'),
            ])
            ->allowedIncludes($includes)
            ->defaultSort('-is_pinned', '-last_activity_at');

        if ($search) {
            $threadsQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $threads = $threadsQuery->paginate($perPage);
        
        $threads->getCollection()->transform(fn ($item) => new ThreadResource($item));

        return $threads;
    }

    public function getThreadDetail(int $threadId): Thread
    {
        $includes = array_merge(
            ['author', 'course', 'media', 'tags'],
            $this->getRecursiveReplyIncludes()
        );

        return QueryBuilder::for(Thread::class)
            ->where('id', $threadId)
            ->allowedIncludes($includes)
            ->firstOrFail();
    }

    public function getThreadSummary(int $threadId): Thread
    {
        return QueryBuilder::for(Thread::class)
            ->where('id', $threadId)
            ->allowedIncludes(['author', 'course', 'media', 'tags'])
            ->firstOrFail();
    }

    /**
     * Generates allowed includes for nested replies up to a specific depth.
     * This ensures the API can handle deep nesting requests without hardcoded strings.
     */
    private function getRecursiveReplyIncludes(int $depth = 10): array
    {
        $includes = [
            'replies', // Flat
            'replies.author',
            'replies.media',
            'topLevelReplies', // Root
            'topLevelReplies.author',
            'topLevelReplies.media',
        ];

        $currentPrefix = 'topLevelReplies';

        for ($i = 0; $i < $depth; $i++) {
            $currentPrefix .= '.children';
            $includes[] = $currentPrefix;
            $includes[] = $currentPrefix . '.author';
            $includes[] = $currentPrefix . '.media';
        }

        return $includes;
    }
}
