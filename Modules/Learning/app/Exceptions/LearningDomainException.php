<?php

declare(strict_types=1);

namespace Modules\Learning\Exceptions;

use Exception;

class LearningDomainException extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, private array $errors = [])
    {
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        $status = $this->getCode() ?: ($this instanceof SubmissionException ? 422 : 400);

        $response = [
            'status' => 'error',
            'message' => $this->getMessage(),
        ];

        if (! empty($this->errors)) {
            $response['errors'] = $this->errors;
            if (array_key_exists('latest_submission_id', $this->errors)) {
                $response['latest_submission_id'] = $this->errors['latest_submission_id'];
            }
            $status = 422;
        }

        return response()->json($response, $status);
    }
}
