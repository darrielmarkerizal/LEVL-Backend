<x-mail::email-layout :title="__('mail.enrollment_active.title')">
    <h1>{{ __('mail.enrollment_active.title') }}</h1>

    <p>{!! __('mail.enrollment_active.greeting', ['name' => $student->name]) !!}</p>
    <p>{{ __('mail.enrollment_active.body') }}</p>

    <x-mail::course-box :title="$course->title" :code="$course->code" />

    <x-mail::info-box type="success">
        {!! __('mail.enrollment_active.info') !!}
    </x-mail::info-box>

    <x-mail::button :url="$courseUrl" :text="__('mail.enrollment_active.button')" />

    <x-mail::url-box :url="$courseUrl" />

    <x-slot name="footer">
        <p>{{ __('mail.enrollment_active.footer') }}</p>
    </x-slot>
</x-mail::email-layout>
