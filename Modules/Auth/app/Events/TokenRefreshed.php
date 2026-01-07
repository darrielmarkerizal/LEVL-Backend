<?php

declare(strict_types=1);


namespace Modules\Auth\Events;

class TokenRefreshed
{
    public function __construct(
        public readonly int $userId,
        public readonly int $refreshTokenId,
    ) {}
}


