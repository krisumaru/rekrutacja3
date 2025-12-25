<?php

declare(strict_types=1);

namespace App\ContactMessage\Interface\Sanitizer;

interface SanitizerInterface
{
    public function sanitize(string $value): string;

    /**
     * @param array<string, string> $values
     *
     * @return array<string, string>
     */
    public function sanitizeAll(array $values): array;
}
