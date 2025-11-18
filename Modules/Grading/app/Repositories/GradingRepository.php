<?php

namespace Modules\Grading\Repositories;

class GradingRepository
{
    public function view(string $template): string
    {
        return sprintf('grading::%s', $template);
    }
}
