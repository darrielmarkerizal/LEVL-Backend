<style>
    .url-box {
        background-color: #f8f8f8;
        padding: 12px 16px;
        border-radius: 6px;
        word-break: break-all;
        font-size: 13px;
        margin: 16px 0;
    }
    .url-box a {
        color: #404040;
        text-decoration: none;
    }
</style>

<p style="font-size: 14px; color: #737373; margin: 0 0 8px 0;">{{ $label ?? 'Jika tombol di atas tidak berfungsi, salin dan tempel URL berikut ke browser Anda:' }}</p>
<div class="url-box">
    <a href="{{ $url }}" target="_blank" rel="noopener">{{ $url }}</a>
</div>
