<?php

declare(strict_types=1);

namespace Modules\Mail\Mail\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ChangeEmailVerificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $newEmail,
        public readonly string $verifyUrl,
        public readonly int $ttlMinutes,
        public readonly string $token,
        public readonly string $uuid
    ) {
        $this->onQueue('emails-critical');
    }

    public function build(): self
    {
        return $this->subject('Verifikasi Perubahan Email Anda')
            ->view('mail::emails.auth.change-email-verify')
            ->with([
                'newEmail' => $this->newEmail,
                'verifyUrl' => $this->verifyUrl,
                'ttlMinutes' => $this->ttlMinutes,
                'token' => $this->token,
                'uuid' => $this->uuid,
            ]);
    }
}
