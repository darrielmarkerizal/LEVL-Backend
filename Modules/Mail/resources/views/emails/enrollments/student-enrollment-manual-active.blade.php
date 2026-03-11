@component('mail::components.email-layout', ['title' => __('mail.enrollment_manual_active.title')])
    <h1>{{ __('mail.enrollment_manual_active.title') }}</h1>

    <p>{!! __('mail.enrollment_manual_active.greeting', ['name' => $student->name]) !!}</p>
    <p>{{ __('mail.enrollment_manual_active.body') }}</p>

    @include('mail::components.course-box', [
        'title' => $course->title,
        'code' => $course->code,
    ])

    @component('mail::components.info-box', ['type' => 'success'])
        {!! __('mail.enrollment_manual_active.info') !!}
    @endcomponent

    @include('mail::components.button', [
        'url' => $courseUrl,
        'text' => __('mail.enrollment_manual_active.button'),
    ])

    @include('mail::components.url-box', ['url' => $courseUrl])

    @slot('footer')
        <p>{{ __('mail.enrollment_manual_active.footer') }}</p>
    @endslot
@endcomponent
