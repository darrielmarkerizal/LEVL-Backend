<?php

namespace Modules\Operations\Services;

use Illuminate\Contracts\View\View;
use Modules\Operations\Repositories\OperationsRepository;

class OperationsService
{
    public function __construct(private readonly OperationsRepository $repository) {}

    public function render(string $template, array $data = []): View
    {
        return view($this->repository->view($template), $data);
    }
}
