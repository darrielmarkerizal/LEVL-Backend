<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadService
{
    public function storePublic(UploadedFile $file, string $directory, ?string $filename = null): string
    {
        $disk = Storage::disk('public');
        $name = $filename ?: $this->generateFilename($file);
        $path = trim($directory, '/').'/'.$name;
        $mime = $file->getMimeType();

        if (is_string($mime) && str_starts_with($mime, 'image/')) {
            if (class_exists('\\Intervention\\Image\\ImageManagerStatic')) {
                $quality = (int) env('IMAGE_QUALITY', 80);
                $image = call_user_func(['\\Intervention\\Image\\ImageManagerStatic', 'make'], $file->getRealPath());
                $targetMime = method_exists($image, 'mime') ? $image->mime() : 'jpg';
                $encoded = call_user_func([$image, 'encode'], $targetMime ?: 'jpg', $quality);
                $disk->put($path, (string) $encoded);
            } else {
                $disk->putFileAs(trim($directory, '/'), $file, $name);
            }
        } else {
            $disk->putFileAs(trim($directory, '/'), $file, $name);
        }

        return $path;
    }

    public function deletePublic(?string $path): void
    {
        if (! $path) {
            return;
        }
        $disk = Storage::disk('public');
        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }

    protected function generateFilename(UploadedFile $file): string
    {
        $ext = $file->getClientOriginalExtension() ?: $file->extension();

        return uniqid('file_', true).($ext ? '.'.$ext : '');
    }
}
