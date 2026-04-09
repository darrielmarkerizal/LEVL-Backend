<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Services\BenchmarkService;

class BenchmarkController extends Controller
{
    public function __construct(
        private readonly BenchmarkService $service
    ) {}

    public function index(): JsonResponse
    {
        $users = $this->service->getBenchmarkUsers();

        return response()->json([
            'data' => $users,
            'count' => $users->count(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->service->createBenchmarkUser(
            $request->only(['name', 'email', 'password', 'username'])
        );

        return response()->json([
            'message' => 'Benchmark user created successfully',
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
            ],
        ], 201);
    }

    public function destroy(): JsonResponse
    {
        $this->service->cleanupDatabase();

        return response()->json([
            'message' => 'Users table truncated successfully',
            'success' => true,
        ]);
    }
}
