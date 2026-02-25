<?php

declare(strict_types=1);

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Modules\Dashboard\Contracts\Services\DashboardServiceInterface;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DashboardServiceInterface $service
    ) {}

    public function index(): JsonResponse
    {
        $actor = auth('api')->user();
        
        $data = $this->service->getDashboardData($actor);
        
        return $this->success($data, 'messages.dashboard.retrieved');
    }
}
