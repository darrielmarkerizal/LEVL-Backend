<?php

declare(strict_types=1);

namespace Modules\Notifications\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Notifications\DTOs\CreatePostDTO;
use Modules\Notifications\Enums\PostStatus;
use Modules\Notifications\Http\Requests\UploadImageRequest;
use Modules\Notifications\Services\PostService;

class PostMediaController extends Controller
{
    use ApiResponse;
    use AuthorizesRequests;

    public function __construct(
        private readonly PostService $service
    ) {}

    
    public function uploadImage(UploadImageRequest $request): JsonResponse
    {
        if (! auth('api')->user()->hasRole('Admin')) {
            return $this->forbidden(__('messages.posts.unauthorized'));
        }

        $postUuid = $request->input('post_uuid');
        $file = $request->file('image');

        
        if ($postUuid) {
            $post = $this->service->repository->findByUuid($postUuid);

            if (! $post) {
                return $this->error(__('messages.posts.not_found'), [], 404);
            }
        } else {
            
            $dto = new CreatePostDTO(
                title: 'temp_upload',
                content: '',
                category: 'system',
                status: PostStatus::DRAFT->value,
                audiences: ['admin'],
                notificationChannels: [],
                isPinned: false,
                scheduledAt: null
            );

            $post = $this->service->createPost($dto, auth('api')->id());
        }

        
        $imageUrl = $this->service->uploadImage($post, $file);

        return $this->success([
            'image_url' => $imageUrl,
            'post_uuid' => $post->uuid,
        ], __('messages.posts.image_uploaded'));
    }
}
