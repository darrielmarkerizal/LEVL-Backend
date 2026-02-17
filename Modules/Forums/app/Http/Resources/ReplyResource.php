<?php

declare(strict_types=1);

namespace Modules\Forums\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReplyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $attachments = $this->whenLoaded('media', function () {
            return $this->getMedia('attachments')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'name' => $media->name,
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                    'url' => $media->original_url,
                    'thumb_url' => $media->getUrl('thumb'),
                    'preview_url' => $media->getUrl('preview'),
                ];
            });
        });

        return [
            'id' => $this->id,
            'thread_id' => $this->thread_id,
            'author' => new AuthorResource($this->whenLoaded('author')),
            'parent_id' => $this->parent_id,
            'content' => $this->content,
            'depth' => $this->depth,
            'is_accepted_answer' => $this->is_accepted_answer,
            'attachments' => $attachments,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'edited_at' => $this->edited_at ?? null,
            'children' => ReplyResource::collection($this->whenLoaded('children')),
            'reactions_counts' => [
                'like' => $this->reactions->where('type', 'like')->count(),
                'helpful' => $this->reactions->where('type', 'helpful')->count(),
            ],
            'user_reactions' => $this->reactions->where('user_id', auth('api')->id())->map(function ($reaction) {
                return ['id' => $reaction->id, 'type' => $reaction->type];
            })->values(),
            'deleted_at' => $this->deleted_at,
        ];
    }
}
