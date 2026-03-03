<?php

declare(strict_types=1);

namespace Modules\Schemes\Exceptions;

use Exception;

class LessonCompletionException extends Exception
{
    public static function lessonLocked(string $message): self
    {
        return new self($message, 403);
    }

    public static function alreadyCompleted(string $message): self
    {
        return new self($message, 422);
    }

    public static function notCompleted(string $message): self
    {
        return new self($message, 422);
    }
}
