@component('mail::components.email-layout', ['title' => __('mail.change_email.title')])
    <h1>{{ __('mail.change_email.title') }}</h1>
    
    <p>Halo,</p>
    <p>Kami menerima permintaan untuk mengubah email akun Anda menjadi: <strong>{{ $newEmail }}</strong></p>
    <p>Silakan klik tombol di bawah ini untuk memverifikasi perubahan email Anda.</p>
    
    @include('mail::components.button', [
        'url' => $verifyUrl,
        'text' => 'Verifikasi Email Baru',
    ])

    @component('mail::components.info-box', ['type' => 'warning'])
        <p>Link verifikasi ini akan kedaluwarsa dalam {{ $ttlMinutes }} menit.</p>
        <p>Jika Anda tidak melakukan permintaan ini, abaikan email ini.</p>
    @endcomponent

    @include('mail::components.divider')

    @include('mail::components.url-box', ['url' => $verifyUrl])

    @slot('footer')
        <p>Jika Anda mengalami masalah dengan tombol di atas, salin dan tempel URL berikut ke browser Anda.</p>
    @endslot
@endcomponent


