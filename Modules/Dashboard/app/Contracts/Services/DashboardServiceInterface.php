<?php

declare(strict_types=1);

namespace Modules\Dashboard\Contracts\Services;

use Modules\Auth\Models\User;

interface DashboardServiceInterface
{
    public function getDashboardData(User $user): array;
}
