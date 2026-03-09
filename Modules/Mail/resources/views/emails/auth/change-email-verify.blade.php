@component('mail::components.email-layout', ['title' => __('mail.change_email.title')])
    <h1>{{ __('mail.change_email.title') }}</h1>
    
    <p>{!! __('mail.change_email.greeting', ['name' => $user->name]) !!}</p>
    <p>{!! __('mail.change_email.body', ['new_email' => $newEmail]) !!}</p>
    <p>{{ __('mail.change_email.body_confirm') }}</p>
    
    @include('mail::components.button', [
        'url' => $verifyUrl,
        'text' => __('mail.change_email.button'),
    ])

    @component('mail::components.info-box', ['type' => 'warning'])
        {!! __('mail.change_email.info', ['minutes' => $ttlMinutes]) !!}
    @endcomponent

    @include('mail::components.divider')

    @include('mail::components.url-box', ['url' => $verifyUrl])

    @slot('footer')
        <p>{{ __('mail.change_email.footer') }}</p>
    @endslot
@endcomponent


