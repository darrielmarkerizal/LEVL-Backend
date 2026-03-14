<?php

declare(strict_types=1);

namespace Modules\Mail\Mail\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Auth\Models\User;

class VerifyEmailLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $verifyUrl,
        public readonly int $ttlMinutes,
        public readonly string $token,
        public readonly string $uuid
    ) {}

    public function build(): self
    {
        return $this->subject('Verifikasi Email Akun Anda')
            ->view('mail::emails.auth.verify')
            ->with([
                'user' => $this->user,
                'verifyUrl' => $this->verifyUrl,
                'ttlMinutes' => $this->ttlMinutes,
                'token' => $this->token,
                'uuid' => $this->uuid,
            ]);
    }
}
