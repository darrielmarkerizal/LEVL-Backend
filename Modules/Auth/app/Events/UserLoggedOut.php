<?php

declare(strict_types=1);


namespace Modules\Auth\Events;

class UserLoggedOut
{
    public function __construct(
        public readonly int $userId,
    ) {}
}


