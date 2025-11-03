<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Services\AuthService;
use Tymon\JWTAuth\Facades\JWTAuth as JWT;

class AuthApiController extends Controller
{
    public function __construct(private readonly AuthService $auth)
    {
        
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $this->auth->register(
            validated: $request->validated(),
            ip: $request->ip(),
            userAgent: $request->userAgent()
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil',
            'data' => $data,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $login = $request->string('login');

        try {
            $data = $this->auth->login(
                login: $login,
                password: $request->input('password'),
                ip: $request->ip(),
                userAgent: $request->userAgent()
            );
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => $data,
        ], 200);
    }

    public function refresh(Request $request): JsonResponse
    {
        $request->validate([
            'refresh_token' => ['required', 'string'],
        ]);

        if (!auth('api')->check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak terotorisasi: header Authorization Bearer wajib dikirim dan harus valid.',
            ], 401);
        }

        try {
            /** @var \Modules\Auth\Models\User $authUser */
            $authUser = auth('api')->user();
            $data = $this->auth->refresh($authUser, $request->string('refresh_token'));
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Refresh token tidak valid atau tidak cocok dengan akun saat ini.',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Token berhasil diperbarui',
            'data' => $data,
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->validate([
            'refresh_token' => ['nullable', 'string'],
        ]);

        /** @var \Modules\Auth\Models\User|null $user */
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak terotorisasi: header Authorization Bearer wajib dikirim dan harus valid.',
            ], 401);
        }

        $currentJwt = $request->bearerToken();
        if (!$currentJwt) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak terotorisasi: token akses tidak ditemukan di header Authorization.',
            ], 401);
        }

        $this->auth->logout($user, $currentJwt, $request->input('refresh_token'));

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil',
        ], 200);
    }

    public function profile(): JsonResponse
    {
        /** @var \Modules\Auth\Models\User|null $user */
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak terotorisasi: header Authorization Bearer wajib dikirim dan harus valid.',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diambil',
            'data' => $user,
        ], 200);
    }
}


