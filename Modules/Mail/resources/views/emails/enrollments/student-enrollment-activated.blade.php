@component('mail::components.email-layout', ['title' => __('mail.enrollment_activated.title')])
    <h1>{{ __('mail.enrollment_activated.title') }}</h1>

    <p>{!! __('mail.enrollment_activated.greeting', ['name' => $student->name]) !!}</p>
    <p>{{ __('mail.enrollment_activated.body') }}</p>

    @include('mail::components.course-box', [
        'title' => $course->title,
        'code' => $course->code,
    ])

    @component('mail::components.info-box', ['type' => 'success'])
        {!! __('mail.enrollment_activated.info') !!}
    @endcomponent

    @include('mail::components.button', [
        'url' => $courseUrl,
        'text' => __('mail.enrollment_activated.button'),
    ])

    @include('mail::components.url-box', ['url' => $courseUrl])

    @slot('footer')
        <p>{{ __('mail.enrollment_activated.footer') }}</p>
    @endslot
@endcomponent
