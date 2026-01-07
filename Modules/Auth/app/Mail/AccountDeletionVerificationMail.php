<?php

declare(strict_types=1);

namespace Modules\Auth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Auth\Models\User;

class AccountDeletionVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $verifyUrl,
        public readonly int $ttlMinutes
    ) {}

    public function build(): self
    {
        return $this->subject('Konfirmasi Penghapusan Akun')
            ->view('auth::emails.account-deletion-verify')
            ->with([
                'user' => $this->user,
                'verifyUrl' => $this->verifyUrl,
                'ttlMinutes' => $this->ttlMinutes,
            ]);
    }
}
