<?php

declare(strict_types=1);

namespace Modules\Notifications\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Notifications\DTOs\CreatePostDTO;
use Modules\Notifications\DTOs\UpdatePostDTO;
use Modules\Notifications\Http\Requests\StorePostRequest;
use Modules\Notifications\Http\Requests\UpdatePostRequest;
use Modules\Notifications\Http\Resources\PostListResource;
use Modules\Notifications\Http\Resources\PostResource;
use Modules\Notifications\Services\PostService;

class PostController extends Controller
{
    use ApiResponse;
    use AuthorizesRequests;

    public function __construct(
        private readonly PostService $service
    ) {}

    /**
     * List posts with pagination and filters (status, category, search, role)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 15), 100);
        $search = $request->get('search');
        $role = $request->get('role');

        $paginator = $this->service->repository->paginateWithSearch($perPage, $search, $role);
        $paginator->getCollection()->transform(fn ($item) => new PostListResource($item));

        return $this->paginateResponse($paginator, __('messages.posts.retrieved'));
    }

    /**
     * Create new post (admin only)
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        if (!auth('api')->user()->hasRole('Admin')) {
            return $this->forbidden(__('messages.posts.unauthorized'));
        }

        $dto = CreatePostDTO::from($request->validated());
        $post = $this->service->createPost($dto, auth('api')->id());

        return $this->created(new PostResource($post), __('messages.posts.created'));
    }

    /**
     * Get single post (check authorization)
     */
    public function show(string $uuid): JsonResponse
    {
        $post = $this->service->repository->findByUuid($uuid);

        if (!$post) {
            return $this->error(__('messages.posts.not_found'), [], 404);
        }

        // Check if user can view this post
        $user = auth('api')->user();
        $userRole = $user->roles->first()?->name;
        
        if (!$user->hasRole('Admin')) {
            $postAudiences = $post->audiences->pluck('role')->map(fn($r) => $r->value)->toArray();
            if (!in_array($userRole, $postAudiences)) {
                return $this->forbidden(__('messages.posts.unauthorized'));
            }
        }

        return $this->success(new PostResource($post));
    }

    /**
     * Update post (admin only)
     */
    public function update(UpdatePostRequest $request, string $uuid): JsonResponse
    {
        if (!auth('api')->user()->hasRole('Admin')) {
            return $this->forbidden(__('messages.posts.unauthorized'));
        }

        $post = $this->service->repository->findByUuid($uuid);

        if (!$post) {
            return $this->error(__('messages.posts.not_found'), [], 404);
        }

        $dto = UpdatePostDTO::from($request->validated());
        $updated = $this->service->updatePost($post, $dto, auth('api')->id());

        return $this->success(new PostResource($updated), __('messages.posts.updated'));
    }

    /**
     * Soft delete post (admin only)
     */
    public function destroy(string $uuid): JsonResponse
    {
        if (!auth('api')->user()->hasRole('Admin')) {
            return $this->forbidden(__('messages.posts.unauthorized'));
        }

        $post = $this->service->repository->findByUuid($uuid);

        if (!$post) {
            return $this->error(__('messages.posts.not_found'), [], 404);
        }

        $this->service->deletePost($post);

        return $this->success([], __('messages.posts.deleted'));
    }

    /**
     * Publish a post (admin only)
     */
    public function publish(string $uuid): JsonResponse
    {
        if (!auth('api')->user()->hasRole('Admin')) {
            return $this->forbidden(__('messages.posts.unauthorized'));
        }

        $post = $this->service->repository->findByUuid($uuid);

        if (!$post) {
            return $this->error(__('messages.posts.not_found'), [], 404);
        }

        $published = $this->service->publishPost($post);

        return $this->success(new PostResource($published), __('messages.posts.published'));
    }

    /**
     * Unpublish a post (admin only)
     */
    public function unpublish(string $uuid): JsonResponse
    {
        if (!auth('api')->user()->hasRole('Admin')) {
            return $this->forbidden(__('messages.posts.unauthorized'));
        }

        $post = $this->service->repository->findByUuid($uuid);

        if (!$post) {
            return $this->error(__('messages.posts.not_found'), [], 404);
        }

        $unpublished = $this->service->unpublishPost($post);

        return $this->success(new PostResource($unpublished), __('messages.posts.unpublished'));
    }

    /**
     * Schedule a post (admin only, validate scheduled_at)
     */
    public function schedule(Request $request, string $uuid): JsonResponse
    {
        if (!auth('api')->user()->hasRole('Admin')) {
            return $this->forbidden(__('messages.posts.unauthorized'));
        }

        $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
        ]);

        $post = $this->service->repository->findByUuid($uuid);

        if (!$post) {
            return $this->error(__('messages.posts.not_found'), [], 404);
        }

        $scheduled = $this->service->schedulePost($post, $request->input('scheduled_at'));

        return $this->success(new PostResource($scheduled), __('messages.posts.scheduled'));
    }

    /**
     * Cancel scheduling (admin only)
     */
    public function cancelSchedule(string $uuid): JsonResponse
    {
        if (!auth('api')->user()->hasRole('Admin')) {
            return $this->forbidden(__('messages.posts.unauthorized'));
        }

        $post = $this->service->repository->findByUuid($uuid);

        if (!$post) {
            return $this->error(__('messages.posts.not_found'), [], 404);
        }

        $cancelled = $this->service->cancelSchedule($post);

        return $this->success(new PostResource($cancelled), __('messages.posts.schedule_cancelled'));
    }

    /**
     * Toggle pin status (admin only)
     */
    public function togglePin(string $uuid): JsonResponse
    {
        if (!auth('api')->user()->hasRole('Admin')) {
            return $this->forbidden(__('messages.posts.unauthorized'));
        }

        $post = $this->service->repository->findByUuid($uuid);

        if (!$post) {
            return $this->error(__('messages.posts.not_found'), [], 404);
        }

        $toggled = $this->service->togglePin($post);

        return $this->success(new PostResource($toggled), __('messages.posts.pin_toggled'));
    }

    /**
     * Bulk delete posts (admin only, max 50 posts, validate limit)
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        if (!auth('api')->user()->hasRole('Admin')) {
            return $this->forbidden(__('messages.posts.unauthorized'));
        }

        $request->validate([
            'post_uuids' => ['required', 'array', 'max:50'],
            'post_uuids.*' => ['required', 'string', 'exists:posts,uuid'],
        ]);

        $postUuids = $request->input('post_uuids');

        if (count($postUuids) > 50) {
            return $this->validationError(
                ['post_uuids' => [__('messages.posts.bulk_limit_exceeded')]],
                __('messages.validation_failed')
            );
        }

        $this->service->bulkDelete($postUuids);

        return $this->success([], __('messages.posts.bulk_delete_queued'));
    }

    /**
     * Bulk publish posts (admin only, max 50 posts, validate limit)
     */
    public function bulkPublish(Request $request): JsonResponse
    {
        if (!auth('api')->user()->hasRole('Admin')) {
            return $this->forbidden(__('messages.posts.unauthorized'));
        }

        $request->validate([
            'post_uuids' => ['required', 'array', 'max:50'],
            'post_uuids.*' => ['required', 'string', 'exists:posts,uuid'],
        ]);

        $postUuids = $request->input('post_uuids');

        if (count($postUuids) > 50) {
            return $this->validationError(
                ['post_uuids' => [__('messages.posts.bulk_limit_exceeded')]],
                __('messages.validation_failed')
            );
        }

        $this->service->bulkPublish($postUuids);

        return $this->success([], __('messages.posts.bulk_publish_queued'));
    }

    /**
     * List trashed posts (admin only)
     */
    public function trash(Request $request): JsonResponse
    {
        if (!auth('api')->user()->hasRole('Admin')) {
            return $this->forbidden(__('messages.posts.unauthorized'));
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $paginator = $this->service->repository->getTrashedPosts($perPage);
        $paginator->getCollection()->transform(fn ($item) => new PostListResource($item));

        return $this->paginateResponse($paginator, __('messages.posts.trash_retrieved'));
    }

    /**
     * Restore from trash (admin only)
     */
    public function restore(string $uuid): JsonResponse
    {
        if (!auth('api')->user()->hasRole('Admin')) {
            return $this->forbidden(__('messages.posts.unauthorized'));
        }

        $post = $this->service->repository->model()::withTrashed()->where('uuid', $uuid)->first();

        if (!$post) {
            return $this->error(__('messages.posts.not_found'), [], 404);
        }

        $this->service->restorePost($post);

        return $this->success([], __('messages.posts.restored'));
    }

    /**
     * Permanently delete (admin only)
     */
    public function forceDelete(string $uuid): JsonResponse
    {
        if (!auth('api')->user()->hasRole('Admin')) {
            return $this->forbidden(__('messages.posts.unauthorized'));
        }

        $post = $this->service->repository->model()::withTrashed()->where('uuid', $uuid)->first();

        if (!$post) {
            return $this->error(__('messages.posts.not_found'), [], 404);
        }

        $this->service->forceDeletePost($post);

        return $this->success([], __('messages.posts.permanently_deleted'));
    }

    /**
     * Mark post as viewed (authenticated users)
     */
    public function markAsViewed(string $uuid): JsonResponse
    {
        $post = $this->service->repository->findByUuid($uuid);

        if (!$post) {
            return $this->error(__('messages.posts.not_found'), [], 404);
        }

        $this->service->markAsViewed($post, auth('api')->id());

        return $this->success([], __('messages.posts.marked_as_viewed'));
    }
}
