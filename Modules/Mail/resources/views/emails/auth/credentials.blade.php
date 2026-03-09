<x-mail::email-layout :title="__('mail.credentials.title')">
    <h1>{{ __('mail.credentials.title') }}</h1>

    <p>{!! __('mail.credentials.greeting', ['name' => $user->name]) !!}</p>
    <p>{{ __('mail.credentials.body') }}</p>

    <x-mail::code-box :label="__('mail.credentials.email_label')" :value="$user->email" />
    <x-mail::code-box :label="__('mail.credentials.password_label')" :value="$password" />
    
    <x-mail::button :url="$loginUrl" :text="__('mail.credentials.button')" />

    <x-mail::divider />
    
    <x-mail::url-box :url="$loginUrl" />

    <x-slot name="footer">
        <p>{{ __('mail.credentials.footer') }}</p>
    </x-slot>
</x-mail::email-layout>
