@component('mail::components.email-layout', ['title' => __('mail.assignment_published.title')])
    <h1>{{ __('mail.assignment_published.title') }}</h1>
    
    <p>{!! __('mail.assignment_published.greeting', ['name' => $user->name]) !!}</p>
    <p>{{ __('mail.assignment_published.body') }}</p>
    
    @component('mail::components.info-box', ['type' => 'info'])
        <h2 style="font-size: 18px; font-weight: 600; color: #1a1a1a; margin: 0 0 12px 0;">{{ $assignment->title }}</h2>
        @if($assignment->description)
        <p style="font-size: 14px; color: #666666; margin: 8px 0;">{{ Str::limit($assignment->description, 200) }}</p>
        @endif
        <p style="font-size: 14px; color: #666666; margin: 8px 0;"><strong>{{ __('mail.assignment_published.course_label') }}:</strong> {{ $course->title }}</p>
        @if($assignment->available_from)
        <p style="font-size: 14px; color: #666666; margin: 8px 0;"><strong>{{ __('mail.assignment_published.available_from_label') }}:</strong> {{ $assignment->available_from->format('d F Y, H:i') }}</p>
        @endif
        <p style="font-size: 14px; color: #666666; margin: 8px 0;"><strong>{{ __('mail.assignment_published.max_score_label') }}:</strong> {{ $assignment->max_score }}</p>
    @endcomponent

    <p>{{ __('mail.assignment_published.submit_text') }}</p>

    @include('mail::components.button', [
        'url' => $assignmentUrl,
        'text' => __('mail.assignment_published.button_assignment'),
    ])
    @include('mail::components.button-secondary', [
        'url' => $courseUrl,
        'text' => __('mail.assignment_published.button_course'),
    ])

    @slot('footer')
        <p>{{ __('mail.assignment_published.footer') }}</p>
    @endslot
@endcomponent
