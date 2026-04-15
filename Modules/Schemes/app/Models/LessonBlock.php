<?php

declare(strict_types=1);

namespace Modules\Schemes\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Schemes\Enums\BlockType;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class LessonBlock extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['lesson_id', 'slug', 'block_type', 'content', 'order', 'external_url'];

    protected $casts = [
        'order' => 'integer',
        'block_type' => BlockType::class,
    ];

    protected $appends = ['media_url', 'media_thumb_url', 'embed_url'];

    /**
     * Register media collections for this model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('media')
            ->singleFile()
            ->useDisk('do')
            ->acceptsMimeTypes([
                // Images
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/gif',
                'image/webp',
                'image/svg+xml',
                'image/bmp',
                // Videos
                'video/mp4',
                'video/webm',
                'video/ogg',
                'video/quicktime',
                'video/x-msvideo', // .avi
                'video/x-matroska', // .mkv
                // Audio
                'audio/mpeg',
                'audio/wav',
                'audio/ogg',
                'audio/mp3',
                'audio/mp4',
                'audio/aac',
                // Documents
                'application/pdf',
                'text/plain', // .txt
                'text/csv',
                'text/html',
                'application/rtf',
                'text/rtf', // RTF documents (alternative mime type)
                // Microsoft Office
                'application/msword', // .doc
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
                'application/vnd.ms-excel', // .xls
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                'application/vnd.ms-powerpoint', // .ppt
                'application/vnd.openxmlformats-officedocument.presentationml.presentation', // .pptx
                // Archives
                'application/zip',
                'application/x-zip-compressed',
                'application/x-rar-compressed',
                'application/x-rar',
                'application/x-7z-compressed',
                'application/x-tar',
                'application/gzip',
                'application/x-gzip',
                // Other common formats
                'application/json',
                'application/xml',
                'text/xml',
                'application/octet-stream', // Generic binary
            ]);
    }

    /**
     * Register media conversions for this model.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(320)
            ->height(180) // 16:9 ratio for video thumbnails
            ->sharpen(10)
            ->performOnCollections('media');

        // Mobile-optimized thumbnail
        $this->addMediaConversion('mobile')->width(160)->height(90)->performOnCollections('media');

        // Large preview
        $this->addMediaConversion('preview')->width(640)->height(360)->performOnCollections('media');
    }

    public function getMediaUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('media');

        return $media?->getUrl();
    }

    public function getMediaThumbUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('media');

        return $media?->getUrl('thumb');
    }

    public function getMediaMetaAttribute(): ?array
    {
        $media = $this->getFirstMedia('media');
        if (! $media) {
            return null;
        }

        return [
            'name' => $media->file_name,
            'size' => $media->size,
            'mime_type' => $media->mime_type,
            'width' => $media->getCustomProperty('width'),
            'height' => $media->getCustomProperty('height'),
            'duration' => $media->getCustomProperty('duration'),
        ];
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Check if this block uses external URL
     */
    public function isExternalLink(): bool
    {
        return $this->block_type->isExternalLink();
    }

    /**
     * Get embed URL for external links (converts YouTube watch URLs to embed)
     */
    public function getEmbedUrlAttribute(): ?string
    {
        if (!$this->external_url) {
            return null;
        }

        if ($this->block_type === BlockType::YouTube) {
            return $this->convertYouTubeToEmbed($this->external_url);
        }

        if ($this->block_type === BlockType::Drive) {
            return $this->convertDriveToPreview($this->external_url);
        }

        return $this->external_url;
    }

    /**
     * Convert YouTube watch URL to embed URL
     */
    private function convertYouTubeToEmbed(string $url): string
    {
        // Handle youtube.com/watch?v=xxx
        if (preg_match('/[?&]v=([^&]+)/', $url, $matches)) {
            return "https://www.youtube.com/embed/{$matches[1]}";
        }

        // Handle youtu.be/xxx
        if (preg_match('/youtu\.be\/([^?]+)/', $url, $matches)) {
            return "https://www.youtube.com/embed/{$matches[1]}";
        }

        return $url;
    }

    /**
     * Convert Google Drive URL to preview URL
     */
    private function convertDriveToPreview(string $url): string
    {
        // Extract file ID from various Drive URL formats
        if (preg_match('/\/d\/([^\/]+)/', $url, $matches)) {
            return "https://drive.google.com/file/d/{$matches[1]}/preview";
        }

        if (preg_match('/id=([^&]+)/', $url, $matches)) {
            return "https://drive.google.com/file/d/{$matches[1]}/preview";
        }

        return $url;
    }
}
