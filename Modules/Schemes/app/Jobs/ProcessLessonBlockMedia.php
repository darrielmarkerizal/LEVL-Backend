<?php

namespace Modules\Schemes\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Schemes\Models\LessonBlock;

class ProcessLessonBlockMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $blockId) {}

    public function handle(): void
    {
        $block = LessonBlock::find($this->blockId);
        if (! $block || ! $block->getRawOriginal('media_url')) {
            return;
        }

        $path = $block->getRawOriginal('media_url');
        if (! Storage::disk('public')->exists($path)) {
            return;
        }

        $fullPath = Storage::disk('public')->path($path);
        $mime = @mime_content_type($fullPath) ?: null;
        $size = @filesize($fullPath) ?: null;

        $meta = [
            'mime' => $mime,
            'size_bytes' => $size,
        ];

        if ($block->block_type === 'image') {
            $info = @getimagesize($fullPath);
            if ($info) {
                $meta['width'] = $info[0] ?? null;
                $meta['height'] = $info[1] ?? null;
            }
            try {
                if (class_exists('\\Intervention\\Image\\ImageManagerStatic')) {
                    $quality = (int) env('IMAGE_QUALITY', 80);
                    $image = call_user_func(['\\Intervention\\Image\\ImageManagerStatic', 'make'], $fullPath);
                    $targetMime = method_exists($image, 'mime') ? $image->mime() : 'jpg';
                    $image = call_user_func([$image, 'encode'], $targetMime ?: 'jpg', $quality);
                    Storage::disk('public')->put($path, (string) $image);
                }
            } catch (\Throwable $e) {
                Log::info('Image compression skipped: '.$e->getMessage());
            }
        }

        if ($block->block_type === 'video') {
            try {
                $duration = $this->probeDurationSeconds($fullPath);
                if ($duration !== null) {
                    $meta['duration_seconds'] = $duration;
                }
            } catch (\Throwable $e) {
                Log::info('ffprobe unavailable: '.$e->getMessage());
            }
            try {
                $thumbRel = $this->generateVideoThumbnail($path);
                if ($thumbRel) {
                    $block->media_thumbnail_url = $thumbRel;
                    $block->save();
                }
            } catch (\Throwable $e) {
                Log::info('ffmpeg unavailable: '.$e->getMessage());
            }
        }

        try {
            $existing = $block->content;
            $payload = [];
            if (is_string($existing) && str_starts_with(trim($existing), '{')) {
                $decoded = json_decode($existing, true);
                if (is_array($decoded)) {
                    $payload = $decoded;
                }
            }
            $payload['media_meta'] = $meta;
            $block->update(['content' => json_encode($payload)]);
        } catch (\Throwable $e) {
            Log::warning('Gagal menyimpan metadata media blok: '.$e->getMessage());
        }
    }

    protected function probeDurationSeconds(string $fullPath): ?float
    {
        $cmd = sprintf('ffprobe -v error -show_entries format=duration -of default=nokey=1:noprint_wrappers=1 %s', escapeshellarg($fullPath));
        $out = @shell_exec($cmd);
        if ($out) {
            $val = trim($out);
            if (is_numeric($val)) {
                return (float) $val;
            }
        }

        return null;
    }

    protected function generateVideoThumbnail(string $relativePath): ?string
    {
        $disk = Storage::disk('public');
        $input = $disk->path($relativePath);
        $dir = dirname($relativePath);
        $thumbRel = $dir.'/thumb_'.uniqid().'.jpg';
        $thumbAbs = $disk->path($thumbRel);
        $cmd = sprintf('ffmpeg -y -i %s -ss 00:00:01 -vframes 1 %s', escapeshellarg($input), escapeshellarg($thumbAbs));
        @shell_exec($cmd);
        if ($disk->exists($thumbRel)) {
            return $thumbRel;
        }

        return null;
    }
}
