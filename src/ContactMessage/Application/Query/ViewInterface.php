<?php

declare(strict_types=1);

namespace App\ContactMessage\Application\Query;

interface ViewInterface
{
    /**
     * @return array<int|string|bool|null>
     */
    public function toPrimitiveArray(): array;
}
