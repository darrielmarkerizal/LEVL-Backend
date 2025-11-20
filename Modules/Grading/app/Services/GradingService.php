<?php

namespace Modules\Grading\Services;

use Illuminate\Contracts\View\View;
use Modules\Grading\Repositories\GradingRepository;

class GradingService
{
    public function __construct(private readonly GradingRepository $repository) {}

    public function render(string $template, array $data = []): View
    {
        return view($this->repository->view($template), $data);
    }
}
