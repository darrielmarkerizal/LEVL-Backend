<?php

namespace Modules\Notifications\Repositories;

class NotificationsRepository
{
    public function view(string $template): string
    {
        return sprintf('notifications::%s', $template);
    }
}
