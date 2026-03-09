<x-mail::email-layout :title="__('mail.admin_enrollment_notification.title')">
    <h1>{{ __('mail.admin_enrollment_notification.title') }}</h1>

    <p>{!! __('mail.admin_enrollment_notification.greeting', ['name' => $admin->name]) !!}</p>
    <p>{{ __('mail.admin_enrollment_notification.body') }}</p>

    <x-mail::info-container>
        <div class="info-row">
            <div class="info-label">{{ __('mail.admin_enrollment_notification.course_label') }}</div>
            <div class="info-value">{{ $course->title }} ({{ $course->code }})</div>
        </div>
        <div class="info-row">
            <div class="info-label">{{ __('mail.admin_enrollment_notification.student_label') }}</div>
            <div class="info-value">{{ $student->name }} ({{ $student->email }})</div>
        </div>
        <div class="info-row">
            <div class="info-label">{{ __('mail.admin_enrollment_notification.status_label') }}</div>
            <div>
                @if($enrollment->status === \Modules\Enrollments\Enums\EnrollmentStatus::Pending)
                    <x-mail::status-box status="pending">{{ __('mail.admin_enrollment_notification.status_pending') }}</x-mail::status-box>
                @elseif($enrollment->status === \Modules\Enrollments\Enums\EnrollmentStatus::Active)
                    <x-mail::status-box status="active">{{ __('mail.admin_enrollment_notification.status_active') }}</x-mail::status-box>
                @else
                    <x-mail::status-box status="{{ $enrollment->status->value }}">{{ ucfirst($enrollment->status->value) }}</x-mail::status-box>
                @endif
            </div>
        </div>
    </x-mail::info-container>

    @if($enrollment->status === \Modules\Enrollments\Enums\EnrollmentStatus::Pending)
        <p>{{ __('mail.admin_enrollment_notification.body_pending') }}</p>
        <x-mail::button :url="$enrollmentsUrl" :text="__('mail.admin_enrollment_notification.button_manage')" />
    @else
        <p>{{ __('mail.admin_enrollment_notification.body_active') }}</p>
        <x-mail::button :url="$enrollmentsUrl" :text="__('mail.admin_enrollment_notification.button_view')" />
    @endif

    <x-mail::url-box :url="$enrollmentsUrl" />

    <x-slot name="footer">
        <p>{{ __('mail.admin_enrollment_notification.footer', ['app_name' => config('app.name')]) }}</p>
    </x-slot>
</x-mail::email-layout>
