<?php

declare(strict_types=1);

namespace Modules\Common\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Common\Listeners\LogAnswerKeyChanged;
use Modules\Common\Listeners\LogGradeCreated;
use Modules\Common\Listeners\LogGradeOverridden;
use Modules\Common\Listeners\LogOverrideGranted;
use Modules\Common\Listeners\LogSubmissionCreated;
use Modules\Common\Listeners\LogSubmissionStateChanged;
use Modules\Grading\Events\GradeCreated;
use Modules\Grading\Events\GradeOverridden;
use Modules\Learning\Events\AnswerKeyChanged;
use Modules\Learning\Events\OverrideGranted;
use Modules\Learning\Events\SubmissionCreated;
use Modules\Learning\Events\SubmissionStateChanged;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SubmissionCreated::class => [
            LogSubmissionCreated::class,
        ],
        SubmissionStateChanged::class => [
            LogSubmissionStateChanged::class,
        ],
        GradeCreated::class => [
            LogGradeCreated::class,
        ],
        GradeOverridden::class => [
            LogGradeOverridden::class,
        ],
        AnswerKeyChanged::class => [
            LogAnswerKeyChanged::class,
        ],
        OverrideGranted::class => [
            LogOverrideGranted::class,
        ],
    ];

    protected function configureEmailVerification(): void {}
}
