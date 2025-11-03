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

    public function sendVerificationLink(User $user): ?string
    {
        if ($user->email_verified_at && $user->status === 'active') {
            return null;
        }
        OtpCode::query()
            ->forUser($user)
            ->forPurpose(self::PURPOSE)
            ->valid()
            ->update(['consumed_at' => now()]);

        $ttlMinutes = (int) (SystemSetting::get('auth_email_verification_ttl_minutes', 3) ?? 3);

        // 6-digit numeric code
        $code = (string) random_int(100000, 999999);

        $uuid = (string) Str::uuid();

        $otp = OtpCode::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'channel' => 'email',
            'provider' => 'mailhog',
            'purpose' => self::PURPOSE,
            'code' => $code,
            'expires_at' => now()->addMinutes($ttlMinutes),
        ]);

        $baseUrl = rtrim(config('app.url'), '/');
        $verifyUrl = $baseUrl.'/api/v1/auth/email/verify?uuid='.$uuid.'&code='.$otp->code;

        Mail::to($user)->send(new VerifyEmailLinkMail($user, $verifyUrl, $ttlMinutes, $code));

        return $uuid;
    }

    public function verifyByCode(string $uuid, string $code): array
    {
        $otp = OtpCode::query()
            ->forPurpose(self::PURPOSE)
            ->where('uuid', $uuid)
            ->latest('id')
            ->first();

        if (!$otp) {
            return ['status' => 'not_found'];
        }

        if ($otp->isConsumed()) {
            return ['status' => 'invalid'];
        }

        if ($otp->isExpired()) {
            return ['status' => 'expired'];
        }

        if (!hash_equals($otp->code, $code)) {
            return ['status' => 'invalid'];
        }

        $user = User::query()->find($otp->user_id);
        if (!$user) {
            return ['status' => 'not_found'];
        }

        $otp->markAsConsumed();

        if (!$user->email_verified_at || $user->status !== 'active') {
            $user->forceFill([
                'email_verified_at' => now(),
                'status' => 'active',
            ])->save();
        }

        return ['status' => 'ok'];
    }
}


