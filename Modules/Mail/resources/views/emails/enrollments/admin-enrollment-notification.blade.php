@component('mail::components.email-layout', ['title' => __('mail.admin_enrollment_notification.title')])
    <h1>{{ __('mail.admin_enrollment_notification.title') }}</h1>

    <p>{!! __('mail.admin_enrollment_notification.greeting', ['name' => $admin->name]) !!}</p>
    <p>{{ __('mail.admin_enrollment_notification.body') }}</p>

    @component('mail::components.info-container')
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
                    @component('mail::components.status-box', ['status' => 'pending'])
                        {{ __('mail.admin_enrollment_notification.status_pending') }}
                    @endcomponent
                @elseif($enrollment->status === \Modules\Enrollments\Enums\EnrollmentStatus::Active)
                    @component('mail::components.status-box', ['status' => 'active'])
                        {{ __('mail.admin_enrollment_notification.status_active') }}
                    @endcomponent
                @else
                    @component('mail::components.status-box', ['status' => $enrollment->status->value])
                        {{ ucfirst($enrollment->status->value) }}
                    @endcomponent
                @endif
            </div>
        </div>
    @endcomponent

    @if($enrollment->status === \Modules\Enrollments\Enums\EnrollmentStatus::Pending)
        <p>{{ __('mail.admin_enrollment_notification.body_pending') }}</p>
        @include('mail::components.button', [
            'url' => $enrollmentsUrl,
            'text' => __('mail.admin_enrollment_notification.button_manage'),
        ])
    @else
        <p>{{ __('mail.admin_enrollment_notification.body_active') }}</p>
        @include('mail::components.button', [
            'url' => $enrollmentsUrl,
            'text' => __('mail.admin_enrollment_notification.button_view'),
        ])
    @endif

    @include('mail::components.url-box', ['url' => $enrollmentsUrl])

    @slot('footer')
        <p>{{ __('mail.admin_enrollment_notification.footer', ['app_name' => config('app.name')]) }}</p>
    @endslot
@endcomponent
