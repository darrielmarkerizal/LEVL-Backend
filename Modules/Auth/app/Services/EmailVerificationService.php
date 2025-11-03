<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Auth\Models\OtpCode;
use Modules\Auth\Models\User;
use Modules\Common\Models\SystemSetting;
use Modules\Auth\Mail\VerifyEmailLinkMail;

class EmailVerificationService
{
    public const PURPOSE = 'register_verification';

    public function sendVerificationLink(User $user): void
    {
        OtpCode::query()
            ->forUser($user)
            ->forPurpose(self::PURPOSE)
            ->valid()
            ->update(['consumed_at' => now()]);

        $ttlMinutes = (int) (SystemSetting::get('auth_email_verification_ttl_minutes', 3) ?? 3);

        $code = Str::random(20);

        $otp = OtpCode::create([
            'user_id' => $user->id,
            'channel' => 'email',
            'provider' => 'mailhog',
            'purpose' => self::PURPOSE,
            'code' => $code,
            'expires_at' => now()->addMinutes($ttlMinutes),
        ]);

        $baseUrl = rtrim(config('app.url'), '/');
        $verifyUrl = $baseUrl.'/api/v1/auth/email/verify?uid='.$user->id.'&code='.$otp->code;

        Mail::to($user)->queue(new VerifyEmailLinkMail($user, $verifyUrl, $ttlMinutes));
    }

    public function verifyByCode(int $userId, string $code): bool
    {
        $otp = OtpCode::query()
            ->forUser($userId)
            ->forPurpose(self::PURPOSE)
            ->valid()
            ->where('code', $code)
            ->latest('id')
            ->first();

        if (!$otp) {
            return false;
        }

        $otp->markAsConsumed();

        $user = User::query()->find($userId);
        if (!$user) {
            return false;
        }

        if (!$user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return true;
    }
}


