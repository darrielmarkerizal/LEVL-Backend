<?php

declare(strict_types=1);

namespace Modules\Schemes\Enums;

enum BlockType: string
{
    case Text = 'text';
    case Image = 'image';
    case Video = 'video';
    case File = 'file';
    case Link = 'link';
    case YouTube = 'youtube';
    case Drive = 'drive';
    case Embed = 'embed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function rule(): string
    {
        return 'in:'.implode(',', self::values());
    }

    public function label(): string
    {
        return match ($this) {
            self::Text => __('enums.block_type.text'),
            self::Image => __('enums.block_type.image'),
            self::Video => __('enums.block_type.video'),
            self::File => __('enums.block_type.file'),
            self::Link => __('enums.block_type.link'),
            self::YouTube => __('enums.block_type.youtube'),
            self::Drive => __('enums.block_type.drive'),
            self::Embed => __('enums.block_type.embed'),
        };
    }

    public function isExternalLink(): bool
    {
        return in_array($this, [self::Link, self::YouTube, self::Drive, self::Embed]);
    }

    public function requiresMedia(): bool
    {
        return in_array($this, [self::Image, self::Video, self::File]);
    }

    public function requiresExternalUrl(): bool
    {
        return $this->isExternalLink();
    }
}
