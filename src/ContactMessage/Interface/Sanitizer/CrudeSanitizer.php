<?php

declare(strict_types=1);

namespace App\ContactMessage\Interface\Sanitizer;

final readonly class CrudeSanitizer implements SanitizerInterface
{
    private const string PATTERN = '#[^\p{L}\p{N}\r\n\t \,.!\?:;\'"\(\)\[\]\{\}\-_/@\#\$%&\*\+=\\\\|\/<>~`]#u';

    public function sanitize(string $value): string
    {
        $value = trim(strip_tags($value));

        return preg_replace(self::PATTERN, '', $value) ?? '';
    }

    public function sanitizeAll(array $values): array
    {
        return array_map(function ($v) {
            return is_string($v) ? $this->sanitize($v) : $v;
        }, $values);
    }
}
