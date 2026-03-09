@component('mail::components.email-layout', ['title' => __('mail.users_export.title')])
    <h1>{{ __('mail.users_export.title') }}</h1>
    
    <p>{{ __('mail.users_export.greeting') }}</p>
    
    <p>{{ __('mail.users_export.body') }}</p>
    
    @component('mail::components.info-box', ['type' => 'info'])
        <strong>{{ __('mail.users_export.file_label') }}:</strong> {{ $fileName }}
    @endcomponent
    
    @slot('footer')
        <p>{{ __('mail.common.thanks') }},<br>{{ __('mail.common.team', ['app_name' => config('app.name')]) }}</p>
    @endslot
@endcomponent
