@component('mail::components.email-layout', ['title' => __('mail.enrollment_declined.title')])
    <h1>{{ __('mail.enrollment_declined.title') }}</h1>

    <p>{!! __('mail.enrollment_declined.greeting', ['name' => $student->name]) !!}</p>
    <p>{{ __('mail.enrollment_declined.body') }}</p>

    @include('mail::components.course-box', [
        'title' => $course->title,
        'code' => $course->code,
    ])

    @component('mail::components.info-box', ['type' => 'danger'])
        {!! __('mail.enrollment_declined.info') !!}
    @endcomponent

    <p>{{ __('mail.enrollment_declined.contact') }}</p>

    @slot('footer')
        <p>{{ __('mail.enrollment_declined.footer') }}</p>
    @endslot
@endcomponent
