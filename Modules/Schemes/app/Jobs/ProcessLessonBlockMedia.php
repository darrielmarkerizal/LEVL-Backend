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

        $diskName = config('filesystems.default', 'public');
        $disk = Storage::disk($diskName);

        if (! $disk->exists($path)) {
            return;
        }

        $isCloudStorage = in_array($diskName, ['do', 's3']);

        if ($isCloudStorage) {
            $tempPath = sys_get_temp_dir().'/'.uniqid('media_', true).'.'.pathinfo($path, PATHINFO_EXTENSION);
            file_put_contents($tempPath, $disk->get($path));
            $fullPath = $tempPath;
        } else {
            $fullPath = $disk->path($path);
        }

        $mime = @mime_content_type($fullPath) ?: null;
        $size = $isCloudStorage ? strlen($disk->get($path)) : @filesize($fullPath);

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
                    $disk->put($path, (string) $image, 'public');
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
                $thumbRel = $this->generateVideoThumbnail($path, $diskName);
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

        if ($isCloudStorage && isset($tempPath) && file_exists($tempPath)) {
            @unlink($tempPath);
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

    protected function generateVideoThumbnail(string $relativePath, string $diskName): ?string
    {
        $disk = Storage::disk($diskName);
        $isCloudStorage = in_array($diskName, ['do', 's3']);

        if ($isCloudStorage) {
            $tempVideo = sys_get_temp_dir().'/'.uniqid('video_', true).'.'.pathinfo($relativePath, PATHINFO_EXTENSION);
            file_put_contents($tempVideo, $disk->get($relativePath));
            $input = $tempVideo;
        } else {
            $input = $disk->path($relativePath);
        }

        $dir = dirname($relativePath);
        $thumbRel = $dir.'/thumb_'.uniqid().'.jpg';
        $thumbTemp = sys_get_temp_dir().'/'.uniqid('thumb_', true).'.jpg';

        $cmd = sprintf('ffmpeg -y -i %s -ss 00:00:01 -vframes 1 %s', escapeshellarg($input), escapeshellarg($thumbTemp));
        @shell_exec($cmd);

        if (file_exists($thumbTemp)) {
            $disk->put($thumbRel, file_get_contents($thumbTemp), 'public');
            @unlink($thumbTemp);

            if ($isCloudStorage && isset($tempVideo) && file_exists($tempVideo)) {
                @unlink($tempVideo);
            }

            return $thumbRel;
        }

        if ($isCloudStorage && isset($tempVideo) && file_exists($tempVideo)) {
            @unlink($tempVideo);
        }

        return null;
    }
}
