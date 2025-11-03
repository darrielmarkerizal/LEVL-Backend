<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email</title>
    <style>
        body { margin:0; padding:0; font-family: -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; background:#f6f7f9; }
        .wrapper { max-width: 560px; margin: 24px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .header { background: #0ea5e9; color: #ffffff; padding: 16px 24px; font-size: 18px; font-weight: 600; }
        .content { padding: 24px; color: #0f172a; font-size: 14px; line-height: 1.6; }
        .btn { display: inline-block; background: #0ea5e9; color: #ffffff !important; text-decoration: none; padding: 12px 18px; border-radius: 6px; font-weight: 600; margin-top: 16px; }
        .footer { padding: 16px 24px; color: #475569; font-size: 12px; background:#f1f5f9; }
        .note { background: #fef3c7; color: #92400e; padding: 10px 12px; border-radius: 6px; margin-top: 16px; }
    </style>
    <!-- Solid colors only; no gradients as requested -->
    <!-- Intended for MailHog preview -->
    <!-- Simple template -->
</head>
<body>
<div class="wrapper">
    <div class="header">Verifikasi Email Anda</div>
    <div class="content">
        <p>Halo {{ $user->name }},</p>
        <p>Terima kasih telah mendaftar. Silakan verifikasi email Anda dengan mengklik tombol di bawah ini.</p>

        <p><a class="btn" href="{{ $verifyUrl }}" target="_blank" rel="noopener">Verifikasi Email</a></p>

        <div class="note">
            Tautan ini hanya berlaku selama {{ $ttlMinutes }} menit dan hanya dapat digunakan sekali.
        </div>

        <p style="margin-top: 16px; color:#334155;">Jika tombol tidak berfungsi, salin dan tempel URL berikut ke peramban Anda:</p>
        <p style="word-break: break-all;"><a href="{{ $verifyUrl }}" target="_blank" rel="noopener">{{ $verifyUrl }}</a></p>
    </div>
    <div class="footer">Jika Anda tidak merasa membuat akun, abaikan email ini.</div>
</div>
</body>
</html>


