<?php

namespace Modules\Content\DTOs;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapInputName(SnakeCaseMapper::class)]
final class UpdateAnnouncementDTO extends Data
{
    public function __construct(
        #[Max(255)]
        public string|Optional|null $title,

        public string|Optional|null $content,

        #[MapInputName('target_type')]
        public string|Optional|null $targetType,

        #[MapInputName('target_value')]
        public string|Optional|null $targetValue,

        public string|Optional|null $priority,
    ) {}

    public function toModelArray(): array
    {
        $data = [];

        if (! $this->title instanceof Optional) {
            $data['title'] = $this->title;
        }
        if (! $this->content instanceof Optional) {
            $data['content'] = $this->content;
        }
        if (! $this->targetType instanceof Optional) {
            $data['target_type'] = $this->targetType;
        }
        if (! $this->targetValue instanceof Optional) {
            $data['target_value'] = $this->targetValue;
        }
        if (! $this->priority instanceof Optional) {
            $data['priority'] = $this->priority;
        }

        return $data;
    }
}
