<?php

declare(strict_types=1);

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Modules\Auth\Contracts\Repositories\AuthRepositoryInterface;
use Modules\Auth\Contracts\Repositories\PasswordResetTokenRepositoryInterface;
use Modules\Auth\Http\Requests\ChangePasswordRequest;
use Modules\Auth\Http\Requests\ForgotPasswordRequest;
use Modules\Auth\Http\Requests\ResetPasswordRequest;
use Modules\Auth\Models\User;
use Modules\Mail\Mail\Auth\ResetPasswordMail;

class PasswordResetController extends Controller
{
    use ApiResponse;

    public function __construct(
        private PasswordResetTokenRepositoryInterface $passwordResetTokenRepository,
        private AuthRepositoryInterface $authRepository,
    ) {}

    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        
        $user = User::query()
            ->where(fn ($q) => $q->where('email', $validated['login'])->orWhere('username', $validated['login']))
            ->first();

        if (! $user) {
            return $this->success([], __('messages.password.reset_sent'));
        }

        $this->passwordResetTokenRepository->deleteByEmail($user->email);

        $plainToken = bin2hex(random_bytes(32));
        $hashed = Hash::make($plainToken);

        $this->passwordResetTokenRepository->create([
            'email' => $user->email,
            'token' => $hashed,
            'created_at' => now(),
        ]);

        $ttlMinutes = (int) (config('auth.passwords.users.expire', 60) ?? 60);
        $frontendUrl = config('app.frontend_url');
        $resetUrl = $frontendUrl.'/reset-password?token='.$plainToken;

        Mail::to($user)
            ->queue((new ResetPasswordMail($user, $resetUrl, $ttlMinutes))->onQueue('emails-critical'));

        return $this->success([], __('messages.password.reset_sent'));
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $token = $request->input('token');
        $newPassword = $request->input('password');

        $records = $this->passwordResetTokenRepository->findAll();
        $ttlMinutes = (int) (config('auth.passwords.users.expire', 60) ?? 60);
        $matched = null;
        $email = null;

        foreach ($records as $rec) {
            
            $isMatch = false;

            try {
                
                if (str_starts_with($rec->token, '$2y$') || str_starts_with($rec->token, '$2a$') || str_starts_with($rec->token, '$2b$')) {
                    $isMatch = Hash::check($token, $rec->token);
                } else {
                    
                    $isMatch = hash_equals($rec->token, $token);
                }
            } catch (\Exception $e) {
                
                $isMatch = hash_equals($rec->token, $token);
            }

            if ($isMatch) {
                $matched = $rec;
                $email = $rec->email;
                break;
            }
        }

        if (! $matched || ! $email) {
            return $this->error(__('messages.password.token_invalid'), [], 422);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return $this->error(__('messages.user.not_found'), [], 404);
        }

        if (now()->diffInMinutes($matched->created_at) > $ttlMinutes) {
            return $this->error(__('messages.password.token_expired'), [], 422);
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        
        $this->revokeAllUserTokens($user);

        $this->passwordResetTokenRepository->delete($matched);

        return $this->success([], __('messages.password.reset_success'));
    }

    public function confirmForgot(ResetPasswordRequest $request): JsonResponse
    {
        return $this->reset($request);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return $this->error(__('messages.user.not_found'), [], 404);
        }

        if (! Hash::check($request->string('current_password'), $user->password)) {
            return $this->error(__('messages.auth.current_password_incorrect'), [], 422);
        }

        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        
        $this->revokeAllUserTokens($user);

        event(new \Modules\Auth\Events\PasswordChanged($user));

        return $this->success([], __('messages.password.updated'));
    }

    private function revokeAllUserTokens(User $user): void
    {
        try {
            $this->authRepository->revokeAllUserRefreshTokens($user->id);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to revoke tokens for user '.$user->id.': '.$e->getMessage());
        }
    }
}
