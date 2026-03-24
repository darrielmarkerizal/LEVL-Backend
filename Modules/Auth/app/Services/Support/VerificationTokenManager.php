<?php

declare(strict_types=1);

namespace Modules\Auth\Services\Support;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Auth\Enums\UserStatus;
use Modules\Auth\Models\OtpCode;
use Modules\Auth\Models\User;
use Modules\Common\Models\SystemSetting;
use Modules\Mail\Mail\Auth\ChangeEmailVerificationMail;
use Modules\Mail\Mail\Auth\VerifyEmailLinkMail;

class VerificationTokenManager
{
    public const PURPOSE_REGISTER = 'register_verification';

    public const PURPOSE_CHANGE_EMAIL = 'email_change_verification';

    public function sendVerificationLink(User $user): ?string
    {
        if ($user->email_verified_at && $user->status === UserStatus::Active) {
            return null;
        }

        OtpCode::query()
            ->forUser($user)
            ->forPurpose(self::PURPOSE_REGISTER)
            ->valid()
            ->update(['consumed_at' => now()]);

        $ttlMinutes = (int) (SystemSetting::get('auth_email_verification_ttl_minutes', 60) ?? 60);

        $uuid = (string) Str::uuid();
        $token = $this->generateShortToken();
        $tokenHash = hash('sha256', $token);

        OtpCode::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'channel' => 'email',
            'provider' => 'mailhog',
            'purpose' => self::PURPOSE_REGISTER,
            'code' => 'magic',
            'meta' => ['token_hash' => $tokenHash],
            'expires_at' => now()->addMinutes($ttlMinutes),
        ]);

        $verifyUrl = 'levl://verify?'.http_build_query([
            'userId' => $user->id,
            'email' => $user->email,
            'uuid' => $uuid,
            'token' => $token,
        ]);

        Mail::to($user)
            ->queue((new VerifyEmailLinkMail($user, $verifyUrl, $ttlMinutes, $token, $uuid))->onQueue('emails-critical'));

        return $uuid;
    }

    public function sendChangeEmailLink(User $user, string $newEmail): ?string
    {
        OtpCode::query()
            ->forUser($user)
            ->forPurpose(self::PURPOSE_CHANGE_EMAIL)
            ->valid()
            ->update(['consumed_at' => now()]);

        $ttlMinutes = (int) (SystemSetting::get('auth_email_verification_ttl_minutes', 60) ?? 60);

        $uuid = (string) Str::uuid();
        $token = $this->generateShortToken();
        $tokenHash = hash('sha256', $token);

        OtpCode::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'channel' => 'email',
            'provider' => 'mailhog',
            'purpose' => self::PURPOSE_CHANGE_EMAIL,
            'code' => 'magic',
            'meta' => [
                'token_hash' => $tokenHash,
                'new_email' => $newEmail,
            ],
            'expires_at' => now()->addMinutes($ttlMinutes),
        ]);

        $frontendUrl = config('app.frontend_url');
        $queryParams = http_build_query([
            'token' => $token,
            'uuid' => $uuid,
        ]);
        $verifyUrl = $frontendUrl.'/profile/email/verify?'.$queryParams;

        Mail::to($newEmail)
            ->queue((new ChangeEmailVerificationMail($newEmail, $verifyUrl, $ttlMinutes, $token, $uuid))->onQueue('emails-critical'));

        return $uuid;
    }

    private function generateShortToken(): string
    {
        $token = Str::random(16);

        $tokenHash = hash('sha256', $token);
        $exists = OtpCode::query()
            ->whereJsonContains('meta->token_hash', $tokenHash)
            ->exists();

        if ($exists) {
            return $this->generateShortToken();
        }

        return $token;
    }
}
