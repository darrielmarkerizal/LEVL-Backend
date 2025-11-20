<?php

namespace Modules\Notifications\Services;

use Illuminate\Contracts\View\View;
use Modules\Notifications\Repositories\NotificationsRepository;

class NotificationsService
{
    public function __construct(private readonly NotificationsRepository $repository) {}

    public function render(string $template, array $data = []): View
    {
        return view($this->repository->view($template), $data);
    }
}
