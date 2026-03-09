<x-mail::email-layout :title="__('mail.enrollment_pending.title')">
    <h1>{{ __('mail.enrollment_pending.title') }}</h1>

    <p>{!! __('mail.enrollment_pending.greeting', ['name' => $student->name]) !!}</p>
    <p>{{ __('mail.enrollment_pending.body') }}</p>

    <x-mail::course-box :title="$course->title" :code="$course->code" />

    <x-mail::info-box type="warning">
        {!! __('mail.enrollment_pending.info') !!}
    </x-mail::info-box>

    <p>{{ __('mail.enrollment_pending.confirmation') }}</p>

    <x-slot name="footer">
        <p>{{ __('mail.enrollment_pending.footer') }}</p>
    </x-slot>
</x-mail::email-layout>
