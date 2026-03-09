<x-mail::email-layout :title="__('mail.change_email.title')">
    <h1>{{ __('mail.change_email.title') }}</h1>
    
    <p>{!! __('mail.change_email.greeting', ['name' => $user->name]) !!}</p>
    <p>{!! __('mail.change_email.body', ['new_email' => $newEmail]) !!}</p>
    <p>{{ __('mail.change_email.body_confirm') }}</p>
    
    <x-mail::button :url="$verifyUrl" :text="__('mail.change_email.button')" />

    <x-mail::info-box type="warning">
        {!! __('mail.change_email.info', ['minutes' => $ttlMinutes]) !!}
    </x-mail::info-box>

    <x-mail::divider />

    <x-mail::url-box :url="$verifyUrl" />

    <x-slot name="footer">
        <p>{{ __('mail.change_email.footer') }}</p>
    </x-slot>
</x-mail::email-layout>


