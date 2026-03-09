@component('mail::components.email-layout', ['title' => __('mail.reset.title')])
    <h1>{{ __('mail.reset.title') }}</h1>
    
    <p>{!! __('mail.reset.greeting', ['name' => $user->name]) !!}</p>
    <p>{{ __('mail.reset.body') }}</p>

    @include('mail::components.button', [
        'url' => $resetUrl,
        'text' => __('mail.reset.button'),
    ])

    @component('mail::components.info-box', ['type' => 'warning'])
        {!! __('mail.reset.info', ['minutes' => $ttlMinutes]) !!}
    @endcomponent

    @include('mail::components.divider')

    @include('mail::components.url-box', ['url' => $resetUrl])

    @slot('footer')
        <p>{{ __('mail.reset.footer') }}</p>
    @endslot
@endcomponent


