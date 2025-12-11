<?php

namespace Modules\Schemes\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Schemes\Models\LessonBlock;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LessonBlockService
{
    /**
     * List lesson blocks for a lesson.
     *
     * Supports:
     * - filter[lesson_id], filter[block_type]
     * - sort: order, created_at (prefix with - for desc)
     */
    public function list(int $lessonId): Collection
    {
        $query = QueryBuilder::for(LessonBlock::class)
            ->where('lesson_id', $lessonId)
            ->allowedFilters([
                AllowedFilter::exact('block_type'),
            ])
            ->allowedSorts(['order', 'created_at'])
            ->defaultSort('order');

        return $query->get();
    }

    public function create(int $lessonId, array $data, ?UploadedFile $mediaFile): LessonBlock
    {
        return DB::transaction(function () use ($lessonId, $data, $mediaFile) {
            $nextOrder = LessonBlock::where('lesson_id', $lessonId)->max('order');
            $nextOrder = $nextOrder ? $nextOrder + 1 : 1;

            $block = LessonBlock::create([
                'lesson_id' => $lessonId,
                'slug' => (string) Str::uuid(),
                'block_type' => $data['type'],
                'content' => $data['content'] ?? null,
                'order' => $data['order'] ?? $nextOrder,
            ]);

            // Add media using Spatie Media Library
            if ($mediaFile && in_array($data['type'], ['image', 'video', 'file'])) {
                $media = $block
                    ->addMedia($mediaFile)
                    ->toMediaCollection('media');

                // Store additional metadata for video files
                if ($data['type'] === 'video') {
                    $this->storeVideoMetadata($media);
                }
            }

            return $block->fresh();
        });
    }

    public function update(int $lessonId, int $blockId, array $data, ?UploadedFile $mediaFile): LessonBlock
    {
        return DB::transaction(function () use ($lessonId, $blockId, $data, $mediaFile) {
            $block = LessonBlock::where('lesson_id', $lessonId)->findOrFail($blockId);

            $update = [
                'block_type' => $data['type'] ?? $block->block_type,
                'content' => array_key_exists('content', $data) ? $data['content'] : $block->content,
            ];

            if (array_key_exists('order', $data)) {
                $update['order'] = $data['order'];
            }

            $block->update($update);

            // Handle media update using Spatie Media Library
            if ($mediaFile) {
                // Clear existing media (singleFile handles this, but explicit is cleaner)
                $block->clearMediaCollection('media');

                $media = $block
                    ->addMedia($mediaFile)
                    ->toMediaCollection('media');

                // Store additional metadata for video files
                $blockType = $data['type'] ?? $block->block_type;
                if ($blockType === 'video') {
                    $this->storeVideoMetadata($media);
                }
            }

            return $block->fresh();
        });
    }

    public function delete(int $lessonId, int $blockId): bool
    {
        $block = LessonBlock::where('lesson_id', $lessonId)->findOrFail($blockId);

        // Media Library automatically cleans up media when model is deleted
        return (bool) $block->delete();
    }

    /**
     * Store video metadata as custom properties on the media item.
     */
    private function storeVideoMetadata($media): void
    {
        try {
            $path = $media->getPath();
            $ffprobe = config('media-library.ffprobe_path', '/usr/bin/ffprobe');

            if (file_exists($path) && is_executable($ffprobe)) {
                $cmd = sprintf(
                    '%s -v quiet -print_format json -show_format -show_streams %s',
                    escapeshellarg($ffprobe),
                    escapeshellarg($path)
                );

                $output = shell_exec($cmd);
                if ($output) {
                    $data = json_decode($output, true);
                    if (isset($data['format']['duration'])) {
                        $media->setCustomProperty('duration', (float) $data['format']['duration']);
                    }
                    if (isset($data['streams'][0])) {
                        $stream = $data['streams'][0];
                        if (isset($stream['width'])) {
                            $media->setCustomProperty('width', (int) $stream['width']);
                        }
                        if (isset($stream['height'])) {
                            $media->setCustomProperty('height', (int) $stream['height']);
                        }
                    }
                    $media->save();
                }
            }
        } catch (\Throwable $e) {
            // Silently fail - metadata is optional
            \Log::warning('Failed to extract video metadata: '.$e->getMessage());
        }
    }
}
