<x-mail::email-layout :title="__('mail.enrollment_declined.title')">
    <h1>{{ __('mail.enrollment_declined.title') }}</h1>

    <p>{!! __('mail.enrollment_declined.greeting', ['name' => $student->name]) !!}</p>
    <p>{{ __('mail.enrollment_declined.body') }}</p>

    <x-mail::course-box :title="$course->title" :code="$course->code" />

    <x-mail::info-box type="danger">
        {!! __('mail.enrollment_declined.info') !!}
    </x-mail::info-box>

    <p>{{ __('mail.enrollment_declined.contact') }}</p>

    <x-slot name="footer">
        <p>{{ __('mail.enrollment_declined.footer') }}</p>
    </x-slot>
</x-mail::email-layout>
