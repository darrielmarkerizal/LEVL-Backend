<?php

declare(strict_types=1);

namespace Modules\Mail\Mail\Enrollments;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Auth\Models\User;
use Modules\Schemes\Models\Course;

class StudentEnrollmentScheduledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $student,
        public readonly Course $course,
        public readonly Carbon $enrollmentDate,
        public readonly string $courseUrl
    ) {
        $this->onQueue('mail');
    }

    public function build(): self
    {
        return $this->subject(__('mail.enrollment_scheduled.subject'))
            ->view('mail::emails.enrollments.student-enrollment-scheduled')
            ->with([
                'student' => $this->student,
                'course' => $this->course,
                'enrollmentDate' => $this->enrollmentDate,
                'courseUrl' => $this->courseUrl,
            ]);
    }
}
