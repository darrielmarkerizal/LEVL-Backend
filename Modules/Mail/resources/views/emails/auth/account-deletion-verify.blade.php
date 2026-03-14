@component('mail::components.email-layout', ['title' => __('mail.delete_account.title')])
    <h1 style="color: #dc2626;">{{ __('mail.delete_account.title') }}</h1>
    
    <p>{!! __('mail.delete_account.greeting', ['name' => $userName]) !!}</p>
    <p>{{ __('mail.delete_account.body_1') }}</p>
    <p>{!! __('mail.delete_account.body_2') !!}</p>
    
    @include('mail::components.button-danger', [
        'url' => $verifyUrl,
        'text' => __('mail.delete_account.button'),
    ])

    @component('mail::components.info-box', ['type' => 'danger'])
        {!! __('mail.delete_account.info', ['minutes' => $ttlMinutes]) !!}
    @endcomponent

    @include('mail::components.divider')

    <p style="font-size: 14px; color: #737373;">{{ __('mail.delete_account.warning') }}</p>

    @slot('footer')
        <p>{{ __('mail.delete_account.footer') }}</p>
    @endslot
@endcomponent
