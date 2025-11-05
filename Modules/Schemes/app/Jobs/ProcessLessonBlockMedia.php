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
        $mime = mime_content_type($fullPath) ?: null;
        $size = filesize($fullPath) ?: null;

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
}
