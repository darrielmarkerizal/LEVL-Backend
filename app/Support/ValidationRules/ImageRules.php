<?php

namespace App\Support\ValidationRules;

/**
 * Reusable validation rules for image uploads.
 *
 * Centralizes common image validation rules to ensure consistency
 * across the application and reduce code duplication.
 */
class ImageRules
{
    /**
     * Avatar image validation rules.
     *
     * @return array<int, mixed>
     */
    public static function avatar(): array
    {
        return [
            'required',
            'image',
            'mimes:jpeg,png,jpg,gif',
            'max:2048', // 2MB
        ];
    }

    /**
     * Optional avatar image validation rules.
     *
     * @return array<int, mixed>
     */
    public static function avatarOptional(): array
    {
        return [
            'nullable',
            'image',
            'mimes:jpeg,png,jpg,gif',
            'max:2048',
        ];
    }

    /**
     * Course thumbnail validation rules.
     *
     * @return array<int, mixed>
     */
    public static function thumbnail(): array
    {
        return [
            'nullable',
            'image',
            'mimes:jpeg,png,jpg',
            'max:5120', // 5MB
        ];
    }

    /**
     * Banner image validation rules (larger size allowed).
     *
     * @return array<int, mixed>
     */
    public static function banner(): array
    {
        return [
            'nullable',
            'image',
            'mimes:jpeg,png,jpg',
            'max:10240', // 10MB
        ];
    }

    /**
     * General content image validation rules.
     *
     * @return array<int, mixed>
     */
    public static function content(): array
    {
        return [
            'nullable',
            'image',
            'mimes:jpeg,png,jpg,gif,webp',
            'max:5120',
        ];
    }

    /**
     * Profile picture validation rules (required).
     *
     * @return array<int, mixed>
     */
    public static function profilePicture(): array
    {
        return [
            'required',
            'image',
            'mimes:jpeg,png,jpg',
            'max:3072', // 3MB
            'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
        ];
    }

    /**
     * Icon/small image validation rules.
     *
     * @return array<int, mixed>
     */
    public static function icon(): array
    {
        return [
            'nullable',
            'image',
            'mimes:jpeg,png,jpg,svg',
            'max:512', // 512KB
        ];
    }

    /**
     * Custom image validation with specified max size.
     *
     * @param  int  $maxSizeKb  Maximum file size in KB
     * @param  array<string>  $mimes  Allowed mime types
     * @param  bool  $required  Whether the image is required
     * @return array<int, mixed>
     */
    public static function custom(
        int $maxSizeKb = 2048,
        array $mimes = ['jpeg', 'png', 'jpg'],
        bool $required = false
    ): array {
        return [
            $required ? 'required' : 'nullable',
            'image',
            'mimes:'.implode(',', $mimes),
            'max:'.$maxSizeKb,
        ];
    }
}
