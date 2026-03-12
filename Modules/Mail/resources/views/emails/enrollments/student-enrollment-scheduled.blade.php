@component('mail::components.email-layout', ['title' => __('mail.enrollment_scheduled.title')])
    <h1>{{ __('mail.enrollment_scheduled.title') }}</h1>

    <p>{!! __('mail.enrollment_scheduled.greeting', ['name' => $student->name]) !!}</p>
    <p>{{ __('mail.enrollment_scheduled.body') }}</p>

    @include('mail::components.course-box', [
        'title' => $course->title,
        'code' => $course->code,
    ])

    @component('mail::components.info-box', ['type' => 'info'])
        {!! __('mail.enrollment_scheduled.info', ['date' => $enrollmentDate->format('d F Y')]) !!}
    @endcomponent

    <p>{{ __('mail.enrollment_scheduled.note') }}</p>

    @include('mail::components.button', [
        'url' => $courseUrl,
        'text' => __('mail.enrollment_scheduled.button'),
    ])

    @include('mail::components.url-box', ['url' => $courseUrl])

    @slot('footer')
        <p>{{ __('mail.enrollment_scheduled.footer') }}</p>
    @endslot
@endcomponent
