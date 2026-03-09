<x-mail::email-layout :title="__('mail.reset.title')">
    <h1>{{ __('mail.reset.title') }}</h1>
    
    <p>{!! __('mail.reset.greeting', ['name' => $user->name]) !!}</p>
    <p>{{ __('mail.reset.body') }}</p>

    <x-mail::button :url="$resetUrl" :text="__('mail.reset.button')" />

    <x-mail::info-box type="warning">
        {!! __('mail.reset.info', ['minutes' => $ttlMinutes]) !!}
    </x-mail::info-box>

    <x-mail::divider />

    <x-mail::url-box :url="$resetUrl" />

    <x-slot name="footer">
        <p>{{ __('mail.reset.footer') }}</p>
    </x-slot>
</x-mail::email-layout>


