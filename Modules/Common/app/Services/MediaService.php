<?php

declare(strict_types=1);

namespace Modules\Common\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Common\Models\TemporaryMedia;
use Modules\Common\Services\Contracts\MediaServiceInterface;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaService implements MediaServiceInterface
{
    public function upload(UploadedFile $file, User $actor, string $collection = 'default'): Media
    {
        return DB::transaction(function () use ($file, $actor, $collection) {
            $temporary = TemporaryMedia::create([
                'user_id' => $actor->id,
            ]);

            return $temporary->addMedia($file)
                ->toMediaCollection($collection);
        });
    }
}
