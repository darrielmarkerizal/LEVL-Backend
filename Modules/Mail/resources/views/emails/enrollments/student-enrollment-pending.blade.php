@component('mail::components.email-layout', ['title' => __('mail.enrollment_pending.title')])
    <h1>{{ __('mail.enrollment_pending.title') }}</h1>

    <p>{!! __('mail.enrollment_pending.greeting', ['name' => $student->name]) !!}</p>
    <p>{{ __('mail.enrollment_pending.body') }}</p>

    @include('mail::components.course-box', [
        'title' => $course->title,
        'code' => $course->code,
    ])

    @component('mail::components.info-box', ['type' => 'warning'])
        {!! __('mail.enrollment_pending.info') !!}
    @endcomponent

    <p>{{ __('mail.enrollment_pending.confirmation') }}</p>

    @slot('footer')
        <p>{{ __('mail.enrollment_pending.footer') }}</p>
    @endslot
@endcomponent
