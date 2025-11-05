<?php

namespace Modules\Schemes\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Schemes\Models\Lesson;

class LessonCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Lesson $lesson,
        public int $userId,
        public int $enrollmentId
    ) {}
}
