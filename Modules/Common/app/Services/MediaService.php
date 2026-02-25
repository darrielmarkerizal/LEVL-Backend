<?php

declare(strict_types=1);

namespace Modules\Common\app\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Modules\Common\app\Services\Contracts\MediaServiceInterface;
use Modules\Common\Models\TemporaryMedia;
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
