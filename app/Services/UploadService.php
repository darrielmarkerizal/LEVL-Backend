<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadService
{
    protected string $defaultDisk;

    public function __construct()
    {
        $this->defaultDisk = config('filesystems.default', 'do');
    }

    public function storePublic(UploadedFile $file, string $directory, ?string $filename = null, ?string $disk = null): string
    {
        $diskName = $disk ?: $this->defaultDisk;
        $storage = Storage::disk($diskName);
        $name = $filename ?: $this->generateFilename($file);
        $path = trim($directory, '/').'/'.$name;
        $mime = $file->getMimeType();

        if (is_string($mime) && str_starts_with($mime, 'image/')) {
            if (class_exists('\\Intervention\\Image\\ImageManagerStatic')) {
                $quality = (int) env('IMAGE_QUALITY', 80);
                $image = call_user_func(['\\Intervention\\Image\\ImageManagerStatic', 'make'], $file->getRealPath());
                $targetMime = method_exists($image, 'mime') ? $image->mime() : 'jpg';
                $encoded = call_user_func([$image, 'encode'], $targetMime ?: 'jpg', $quality);
                $storage->put($path, (string) $encoded, 'public');
            } else {
                $storage->putFileAs(trim($directory, '/'), $file, $name, 'public');
            }
        } else {
            $storage->putFileAs(trim($directory, '/'), $file, $name, 'public');
        }

        return $path;
    }

    public function deletePublic(?string $path, ?string $disk = null): void
    {
        if (! $path) {
            return;
        }

        $diskName = $disk ?: $this->defaultDisk;
        $storage = Storage::disk($diskName);

        if ($storage->exists($path)) {
            $storage->delete($path);
        }
    }

    public function getPublicUrl(?string $path, ?string $disk = null): ?string
    {
        if (! $path) {
            return null;
        }

        $diskName = $disk ?: $this->defaultDisk;

        if ($diskName === 'public') {
            return asset('storage/'.$path);
        }

        $config = config("filesystems.disks.{$diskName}");

        if (isset($config['url'])) {
            return rtrim($config['url'], '/').'/'.ltrim($path, '/');
        }

        return null;
    }

    public function exists(string $path, ?string $disk = null): bool
    {
        $diskName = $disk ?: $this->defaultDisk;

        return Storage::disk($diskName)->exists($path);
    }

    protected function generateFilename(UploadedFile $file): string
    {
        $ext = $file->getClientOriginalExtension() ?: $file->extension();

        return uniqid('file_', true).($ext ? '.'.$ext : '');
    }
}
