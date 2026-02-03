<?php

namespace Modules\Forums\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Forums\Models\Thread;

class ThreadPinned
{
    use Dispatchable, SerializesModels;

    public Thread $thread;

     
    public function __construct(Thread $thread)
    {
        $this->thread = $thread;
    }
}
