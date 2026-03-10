<?php

declare(strict_types=1);

namespace Modules\Common\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\Common\Http\Requests\UploadMediaRequest;
use Modules\Common\Http\Resources\MediaResource;
use Modules\Common\Services\Contracts\MediaServiceInterface;

class MediaController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly MediaServiceInterface $mediaService
    ) {}

    public function upload(UploadMediaRequest $request): JsonResponse
    {
        $actor = auth('api')->user();
        $file = $request->file('file');

        $media = $this->mediaService->upload($file, $actor, 'globalmedia');

        return $this->created(
            new MediaResource($media),
            'messages.media.uploaded'
        );
    }
}
