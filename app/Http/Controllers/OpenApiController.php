<?php

namespace App\Http\Controllers;

use App\Services\OpenApiGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpenApiController extends Controller
{
    public function __construct(private OpenApiGeneratorService $generator) {}

    public function index(Request $request): JsonResponse
    {
        $spec = $this->generator->generate();

        return response()->json($spec);
    }
}

