<?php

namespace Modules\Content\Traits;

use Modules\Auth\Models\User;
use Modules\Content\Models\ContentRevision;

trait HasContentRevisions
{
    /**
     * Save content revision.
     */
    public function saveRevision(User $editor, ?string $note = null): void
    {
        ContentRevision::create([
            'content_type' => static::class,
            'content_id' => $this->id,
            'editor_id' => $editor->id,
            'title' => $this->title,
            'content' => $this->content,
            'note' => $note,
        ]);
    }
}
