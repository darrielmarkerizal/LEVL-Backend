@component('mail::components.email-layout', ['title' => __('mail.verify.title')])
    <h1>{{ __('mail.verify.title') }}</h1>
    
    <p>{!! __('mail.verify.greeting', ['name' => $user->name]) !!}</p>
    <p>{{ __('mail.verify.body') }}</p>

    @include('mail::components.button', [
        'url' => $verifyUrl,
        'text' => __('mail.verify.button'),
    ])

    @component('mail::components.info-box', ['type' => 'warning'])
        {!! __('mail.verify.info', ['minutes' => $ttlMinutes]) !!}
    @endcomponent

    @include('mail::components.divider')

    @include('mail::components.url-box', ['url' => $verifyUrl])

    @slot('footer')
        <p>{{ __('mail.verify.footer') }}</p>
    @endslot
@endcomponent