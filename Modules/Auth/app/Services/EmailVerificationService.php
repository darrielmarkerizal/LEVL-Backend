<?php

declare(strict_types=1);

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Auth\Contracts\Services\EmailVerificationServiceInterface;
use Modules\Auth\Enums\UserStatus;
use Modules\Auth\Mail\ChangeEmailVerificationMail;
use Modules\Auth\Mail\VerifyEmailLinkMail;
use Modules\Auth\Models\OtpCode;
use Modules\Auth\Models\User;
use Modules\Common\Models\SystemSetting;

class EmailVerificationService implements EmailVerificationServiceInterface
{
    public const PURPOSE = 'register_verification';

    public const PURPOSE_CHANGE_EMAIL = 'email_change_verification';

    public function sendVerificationLink(User $user): ?string
    {
        if ($user->email_verified_at && $user->status === UserStatus::Active) {
            return null;
        }

        OtpCode::query()
            ->forUser($user)
            ->forPurpose(self::PURPOSE)
            ->valid()
            ->update(['consumed_at' => now()]);

        $ttlMinutes = (int) (SystemSetting::get('auth_email_verification_ttl_minutes', 60) ?? 60);

        $uuid = (string) Str::uuid();
        $token = $this->generateShortToken();
        $tokenHash = hash('sha256', $token);

        $otp = OtpCode::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'channel' => 'email',
            'provider' => 'mailhog',
            'purpose' => self::PURPOSE,
            'code' => 'magic',
            'meta' => ['token_hash' => $tokenHash],
            'expires_at' => now()->addMinutes($ttlMinutes),
        ]);

        $frontendUrl = config('app.frontend_url');
        $verifyUrl = $frontendUrl.'/auth/verify-email?token='.$token.'&uuid='.$uuid;

        Mail::to($user)->send(new VerifyEmailLinkMail($user, $verifyUrl, $ttlMinutes));

        return $uuid;
    }

    public function verifyByCode(string $uuidOrToken, string $code): array
    {
        $isUuid = preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuidOrToken);

        if ($isUuid) {
            $otp = OtpCode::query()
                ->forPurpose(self::PURPOSE)
                ->where('uuid', $uuidOrToken)
                ->latest('id')
                ->first();
        } else {
            if (strlen($uuidOrToken) !== 16) {
                return ['status' => 'invalid'];
            }

            $tokenHash = hash('sha256', $uuidOrToken);

            $otp = OtpCode::query()
                ->forPurpose(self::PURPOSE)
                ->valid()
                ->get()
                ->first(function ($record) use ($tokenHash) {
                    return isset($record->meta['token_hash']) &&
                           hash_equals($record->meta['token_hash'], $tokenHash);
                });
        }

        if (! $otp) {
            return ['status' => 'not_found'];
        }

        if ($otp->isConsumed()) {
            return ['status' => 'invalid'];
        }

        if ($otp->isExpired()) {
            return ['status' => 'expired'];
        }

        if (! hash_equals($otp->code, $code)) {
            return ['status' => 'invalid'];
        }

        $user = User::query()->find($otp->user_id);
        if (! $user) {
            return ['status' => 'not_found'];
        }

        $otp->markAsConsumed();

        if (! $user->email_verified_at || $user->status !== UserStatus::Active) {
            $user->forceFill([
                'email_verified_at' => now(),
                'status' => UserStatus::Active,
            ])->save();
        }

        return ['status' => 'ok'];
    }

    public function verifyByToken(string $token, string $uuid): array
    {
        if (strlen($token) !== 16) {
            return ['status' => 'invalid'];
        }

        $tokenHash = hash('sha256', $token);

        $otp = OtpCode::query()
            ->forPurpose(self::PURPOSE)
            ->where('uuid', $uuid)
            ->valid()
            ->first();

        if (! $otp || ! isset($otp->meta['token_hash']) || ! hash_equals($otp->meta['token_hash'], $tokenHash)) {
            return ['status' => 'not_found'];
        }

        if ($otp->isConsumed()) {
            return ['status' => 'invalid'];
        }

        if ($otp->isExpired()) {
            return ['status' => 'expired'];
        }

        $user = User::query()->find($otp->user_id);
        if (! $user) {
            return ['status' => 'not_found'];
        }

        $otp->markAsConsumed();

        if (! $user->email_verified_at || $user->status !== UserStatus::Active) {
            $user->forceFill([
                'email_verified_at' => now(),
                'status' => UserStatus::Active,
            ])->save();
        }

        return ['status' => 'ok', 'user_id' => $user->id];
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

        $otp = OtpCode::create([
            'uuid' => $uuid,
            'user_id' => $user->id,
            'channel' => 'email',
            'provider' => 'mailhog',
            'purpose' => self::PURPOSE_CHANGE_EMAIL,
            'code' => 'magic',
            'meta' => [
                'token_hash' => $tokenHash,
                'new_email' => $newEmail
            ],
            'expires_at' => now()->addMinutes($ttlMinutes),
        ]);

        $frontendUrl = config('app.frontend_url');
        $verifyUrl = $frontendUrl.'/profile/email/verify?token='.$token.'&uuid='.$uuid;

        Mail::to($newEmail)->send(new ChangeEmailVerificationMail($user, $newEmail, $verifyUrl, $ttlMinutes));

        return $uuid;
    }

    public function verifyChangeByToken(string $token, string $uuid): array
    {
        if (strlen($token) !== 16) {
            return ['status' => 'invalid'];
        }

        $tokenHash = hash('sha256', $token);

        $otp = OtpCode::query()
            ->forPurpose(self::PURPOSE_CHANGE_EMAIL)
            ->where('uuid', $uuid)
            ->valid()
            ->first();

        if (! $otp || ! isset($otp->meta['token_hash']) || ! hash_equals($otp->meta['token_hash'], $tokenHash)) {
            return ['status' => 'not_found'];
        }

        if ($otp->isConsumed()) {
            return ['status' => 'invalid'];
        }

        if ($otp->isExpired()) {
            return ['status' => 'expired'];
        }

        $user = User::query()->find($otp->user_id);
        if (! $user) {
            return ['status' => 'not_found'];
        }

        $newEmail = $otp->meta['new_email'] ?? null;
        if (! $newEmail) {
            return ['status' => 'invalid'];
        }

        if (User::query()->where('email', $newEmail)->where('id', '!=', $user->id)->exists()) {
            return ['status' => 'email_taken'];
        }

        $otp->markAsConsumed();

        $user->forceFill([
            'email' => $newEmail,
            'email_verified_at' => now(),
        ])->save();

        return ['status' => 'ok'];
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
