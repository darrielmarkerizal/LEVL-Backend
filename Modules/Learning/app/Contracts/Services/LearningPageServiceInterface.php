<?php

namespace Modules\Learning\Contracts\Services;

interface LearningPageServiceInterface
{
    public function getLearningPage(int $userId, string $courseSlug, ?string $lessonSlug = null): array;
}
