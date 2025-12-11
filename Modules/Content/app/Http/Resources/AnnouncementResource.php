<?php

namespace Modules\Content\Http\Resources;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class AnnouncementResource extends BaseResource
{
    protected array $defaultRelations = ['author', 'course'];

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'priority' => $this->priority,
            'status' => $this->status,
            'target_type' => $this->target_type,
            'target_roles' => $this->target_roles,
            'views_count' => $this->views_count,
            'published_at' => $this->published_at,
            'scheduled_at' => $this->scheduled_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'author' => $this->whenLoaded('author', function () {
                return [
                    'id' => $this->author->id,
                    'name' => $this->author->name,
                    'email' => $this->author->email,
                ];
            }),
            'course' => $this->whenLoaded('course', function () {
                return [
                    'id' => $this->course->id,
                    'title' => $this->course->title,
                    'code' => $this->course->code,
                ];
            }),
            'reads_count' => $this->when(isset($this->reads_count), $this->reads_count),
        ];
    }
}
