<?php

declare(strict_types=1);

namespace Modules\Common\app\Services\Contracts;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface MediaServiceInterface
{
    public function upload(UploadedFile $file, User $actor, string $collection = 'default'): Media;
}
