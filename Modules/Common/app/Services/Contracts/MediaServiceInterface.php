<?php

declare(strict_types=1);

namespace Modules\Common\Services\Contracts;

use Illuminate\Http\UploadedFile;
use Modules\Auth\Models\User;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface MediaServiceInterface
{
    public function upload(UploadedFile $file, User $actor, string $collection = 'default'): Media;
}
