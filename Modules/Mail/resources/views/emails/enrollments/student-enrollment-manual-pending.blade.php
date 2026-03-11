@component('mail::components.email-layout', ['title' => __('mail.enrollment_manual_pending.title')])
    <h1>{{ __('mail.enrollment_manual_pending.title') }}</h1>

    <p>{!! __('mail.enrollment_manual_pending.greeting', ['name' => $student->name]) !!}</p>
    <p>{{ __('mail.enrollment_manual_pending.body') }}</p>

    @include('mail::components.course-box', [
        'title' => $course->title,
        'code' => $course->code,
    ])

    @component('mail::components.info-box', ['type' => 'info'])
        {!! __('mail.enrollment_manual_pending.info') !!}
    @endcomponent

    <p>{{ __('mail.enrollment_manual_pending.confirmation') }}</p>

    @include('mail::components.url-box', ['url' => $courseUrl])

    @slot('footer')
        <p>{{ __('mail.enrollment_manual_pending.footer') }}</p>
    @endslot
@endcomponent
