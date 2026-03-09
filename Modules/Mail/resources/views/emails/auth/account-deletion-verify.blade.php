<x-mail::email-layout :title="__('mail.delete_account.title')">
    <h1 style="color: #dc2626;">{{ __('mail.delete_account.title') }}</h1>
    
    <p>{!! __('mail.delete_account.greeting', ['name' => $user->name]) !!}</p>
    <p>{{ __('mail.delete_account.body_1') }}</p>
    <p>{!! __('mail.delete_account.body_2') !!}</p>
    
    <x-mail::button-danger :url="$verifyUrl" :text="__('mail.delete_account.button')" />

    <x-mail::info-box type="danger">
        {!! __('mail.delete_account.info', ['minutes' => $ttlMinutes]) !!}
    </x-mail::info-box>

    <x-mail::divider />

    <p style="font-size: 14px; color: #737373;">{{ __('mail.delete_account.warning') }}</p>

    <x-slot name="footer">
        <p>{{ __('mail.delete_account.footer') }}</p>
    </x-slot>
</x-mail::email-layout>
