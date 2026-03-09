@component('mail::components.email-layout', ['title' => __('mail.credentials.title')])
    <h1>{{ __('mail.credentials.title') }}</h1>

    <p>{!! __('mail.credentials.greeting', ['name' => $user->name]) !!}</p>
    <p>{{ __('mail.credentials.body') }}</p>

    @include('mail::components.code-box', [
        'label' => __('mail.credentials.email_label'),
        'value' => $user->email,
    ])

    @include('mail::components.code-box', [
        'label' => __('mail.credentials.password_label'),
        'value' => $password,
    ])

    @include('mail::components.button', [
        'url' => $loginUrl,
        'text' => __('mail.credentials.button'),
    ])

    @include('mail::components.divider')

    @include('mail::components.url-box', [
        'url' => $loginUrl,
    ])

    @slot('footer')
        <p>{{ __('mail.credentials.footer') }}</p>
    @endslot
@endcomponent
