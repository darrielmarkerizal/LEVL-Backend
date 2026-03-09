<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('mail.course_completed.subject') }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            line-height: 1.6;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border: 1px solid #e5e5e5;
        }
        .email-header {
            padding: 32px 40px;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #ffffff;
            text-align: center;
            border-bottom: 1px solid #e5e5e5;
        }
        .logo {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }
        .celebration-icon {
            font-size: 48px;
            margin: 16px 0;
        }
        .email-body {
            padding: 40px;
        }
        .email-body h1 {
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0 0 24px 0;
            text-align: center;
        }
        .congratulations {
            text-align: center;
            font-size: 20px;
            font-weight: 600;
            color: #059669;
            margin: 0 0 24px 0;
        }
        .email-body p {
            font-size: 15px;
            color: #404040;
            margin: 0 0 16px 0;
        }
        .course-info {
            background-color: #f0fdf4;
            border-left: 4px solid #059669;
            padding: 20px;
            margin: 24px 0;
            border-radius: 6px;
        }
        .course-info h2 {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0 0 12px 0;
        }
        .course-info p {
            font-size: 14px;
            color: #166534;
            margin: 8px 0;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 24px 0;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-value {
            font-size: 28px;
            font-weight: 600;
            color: #059669;
            margin: 0;
        }
        .stat-label {
            font-size: 13px;
            color: #666666;
            margin: 4px 0 0 0;
        }
        .btn-success {
            display: inline-block;
            padding: 14px 32px;
            background-color: #059669;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 500;
            margin: 24px auto;
            text-align: center;
        }
        .btn-container {
            text-align: center;
        }
        .email-footer {
            padding: 32px 40px;
            background-color: #fafafa;
            border-top: 1px solid #e5e5e5;
            text-align: center;
        }
        .email-footer p {
            font-size: 13px;
            color: #737373;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1 class="logo">{{ config('app.name', 'Levl') }}</h1>
            <div class="celebration-icon">🎉</div>
        </div>
        <div class="email-body">
            <h1>{{ __('mail.course_completed.title') }}</h1>
            <p class="congratulations">{{ __('mail.course_completed.subtitle') }}</p>
            <p>{!! __('mail.course_completed.greeting', ['name' => $user->name]) !!}</p>
            <p>{{ __('mail.course_completed.body') }}</p>
            
            <div class="course-info">
                <h2>{{ $course->title }}</h2>
                @if($course->code)
                <p><strong>{{ __('mail.course_completed.course_code_label') }}:</strong> {{ $course->code }}</p>
                @endif
                @if($enrollment->completed_at)
                <p><strong>{{ __('mail.course_completed.completed_date_label') }}:</strong> {{ $enrollment->completed_at->format('d F Y, H:i') }}</p>
                @endif
                @php
                    $progress = $enrollment->courseProgress?->progress_percent ?? 0;
                @endphp
                <p><strong>{{ __('mail.course_completed.progress_label') }}:</strong> {{ number_format($progress, 1) }}%</p>
            </div>

            <div class="stats">
                @php
                    $progress = $enrollment->courseProgress?->progress_percent ?? 0;
                @endphp
                <div class="stat-item">
                    <p class="stat-value">{{ number_format($progress, 0) }}%</p>
                    <p class="stat-label">{{ __('mail.course_completed.stat_progress') }}</p>
                </div>
                <div class="stat-item">
                    <p class="stat-value">✓</p>
                    <p class="stat-label">{{ __('mail.course_completed.stat_completed') }}</p>
                </div>
            </div>

            <p>{{ __('mail.course_completed.thanks') }}</p>

            <div class="btn-container">
                <a href="{{ $courseUrl }}" class="btn-success">{{ __('mail.course_completed.button') }}</a>
            </div>
        </div>
        <div class="email-footer">
            <p>{{ __('mail.course_completed.footer') }}</p>
        </div>
    </div>
</body>
</html>
