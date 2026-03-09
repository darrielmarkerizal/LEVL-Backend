<x-mail::email-layout :title="__('mail.users_export.title')">
    <h1>{{ __('mail.users_export.title') }}</h1>
    
    <p>{{ __('mail.users_export.greeting') }}</p>
    
    <p>{{ __('mail.users_export.body') }}</p>
    
    <x-mail::info-box type="info">
        <strong>{{ __('mail.users_export.file_label') }}:</strong> {{ $fileName }}
    </x-mail::info-box>
    
    <x-slot name="footer">
        <p>{{ __('mail.common.thanks') }},<br>{{ __('mail.common.team', ['app_name' => config('app.name')]) }}</p>
    </x-slot>
</x-mail::email-layout>
