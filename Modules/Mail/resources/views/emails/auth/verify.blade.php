<x-mail::email-layout :title="__('mail.verify.title')">
    <h1>{{ __('mail.verify.title') }}</h1>
    
    <p>{!! __('mail.verify.greeting', ['name' => $user->name]) !!}</p>
    <p>{{ __('mail.verify.body') }}</p>

    <x-mail::button :url="$verifyUrl" :text="__('mail.verify.button')" />

    <x-mail::info-box type="warning">
        {!! __('mail.verify.info', ['minutes' => $ttlMinutes]) !!}
    </x-mail::info-box>

    <x-mail::divider />

    <x-mail::url-box :url="$verifyUrl" />

    <x-slot name="footer">
        <p>{{ __('mail.verify.footer') }}</p>
    </x-slot>
</x-mail::email-layout>
    </div>
</body>
</html>