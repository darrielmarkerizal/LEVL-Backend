<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Services\AuthService;
use Modules\Auth\Services\EmailVerificationService;
use Modules\Auth\Http\Responses\ApiResponse;

class AuthApiController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly AuthService $auth, private readonly EmailVerificationService $emailVerification)
    {
        
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $this->auth->register(
            validated: $request->validated(),
            ip: $request->ip(),
            userAgent: $request->userAgent()
        );

        return $this->created($data, 'Registrasi berhasil. Silakan periksa email Anda untuk verifikasi.');
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
            return $this->error('Validasi gagal', 422, $e->errors());
        }

        return $this->success($data, 'Login berhasil.');
    }

    public function refresh(Request $request): JsonResponse
    {
        $request->validate([
            'refresh_token' => ['required', 'string'],
        ]);

        try {
            /** @var \Modules\Auth\Models\User $authUser */
            $authUser = auth('api')->user();
            $data = $this->auth->refresh($authUser, $request->string('refresh_token'));
        } catch (ValidationException $e) {
            return $this->error('Refresh token tidak valid atau tidak cocok dengan akun saat ini.', 401);
        }

        return $this->success($data, 'Token akses berhasil diperbarui.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->validate([
            'refresh_token' => ['nullable', 'string'],
        ]);

        /** @var \Modules\Auth\Models\User|null $user */
        $user = auth('api')->user();
        if (!$user) {
            return $this->error('Tidak terotorisasi. Token akses tidak ditemukan atau tidak valid.', 401);
        }

        $currentJwt = $request->bearerToken();
        if (!$currentJwt) {
            return $this->error('Tidak terotorisasi. Token akses tidak ditemukan di header Authorization.', 401);
        }

        $this->auth->logout($user, $currentJwt, $request->input('refresh_token'));

        return $this->success([], 'Logout berhasil.');
    }

    public function profile(): JsonResponse
    {
        /** @var \Modules\Auth\Models\User|null $user */
        $user = auth('api')->user();
        if (!$user) {
            return $this->error('Tidak terotorisasi. Token akses tidak ditemukan atau tidak valid.', 401);
        }

        return $this->success($user->toArray(), 'Profil berhasil diambil.');
    }

    public function sendEmailVerification(Request $request): JsonResponse
    {
        /** @var \Modules\Auth\Models\User|null $user */
        $user = auth('api')->user();
        if (!$user) {
            return $this->error('Tidak terotorisasi. Token akses tidak ditemukan atau tidak valid.', 401);
        }

        if ($user->email_verified_at) {
            return $this->success([], 'Email Anda sudah terverifikasi.');
        }

        $this->emailVerification->sendVerificationLink($user);

        return $this->success([], 'Tautan verifikasi telah dikirim ke email Anda. Berlaku 3 menit dan hanya bisa digunakan sekali.');
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'uid' => ['required', 'integer'],
            'code' => ['required', 'string'],
        ]);

        $ok = $this->emailVerification->verifyByCode((int) $request->input('uid'), $request->string('code'));

        if (!$ok) {
            return $this->error('Tautan verifikasi tidak valid atau telah kedaluwarsa.', 422);
        }

        return $this->success([], 'Email Anda berhasil diverifikasi.');
    }
}


