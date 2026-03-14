<?php

declare(strict_types=1);

namespace Modules\Learning\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Learning\Models\QuizSubmission;

class QuizCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public QuizSubmission $submission
    ) {}
}
