<?php

declare(strict_types=1);

namespace Modules\Enrollments\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Enrollments\Models\Enrollment;

class EnrollmentActivated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Enrollment $enrollment
    ) {}
}
