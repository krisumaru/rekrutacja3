<?php

declare(strict_types=1);

namespace App\ContactMessage\Interface\Sanitizer;

interface SanitizerInterface
{
    public function sanitize(string $value): string;

    /**
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     */
    public function sanitizeAll(array $values): array;
}
