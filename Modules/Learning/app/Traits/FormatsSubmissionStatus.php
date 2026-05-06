<?php

declare(strict_types=1);

namespace Modules\Learning\Traits;

trait FormatsSubmissionStatus
{
    protected function getSubmissionStatusLabel(mixed $submission, bool $isPassed): string
    {
        return match ($submission->status->value) {
            'draft'     => __('messages.submissions.status_label.draft'),
            'submitted' => __('messages.submissions.status_label.submitted'),
            'graded'    => $isPassed ? __('messages.submissions.status_label.passed') : __('messages.submissions.status_label.failed'),
            'returned'  => __('messages.submissions.status_label.returned'),
            default     => __('messages.submissions.status_label.unknown'),
        };
    }
}
