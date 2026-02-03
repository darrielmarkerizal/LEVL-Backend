<?php

namespace Modules\Forums\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Forums\Models\Reaction;

class ReactionAdded
{
    use Dispatchable, SerializesModels;

    public Reaction $reaction;

     
    public function __construct(Reaction $reaction)
    {
        $this->reaction = $reaction;
    }
}
