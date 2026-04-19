<?php

declare(strict_types=1);

namespace Modules\Schemes\Contracts\Repositories;

use Modules\Schemes\Models\LessonBlock;

interface LessonBlockRepositoryInterface
{
    
    public function getMaxOrderForLesson(int $lessonId): int;

    
    public function create(array $data): LessonBlock;

    
    public function findByLessonAndId(int $lessonId, int $blockId): ?LessonBlock;
}
