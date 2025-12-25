<?php

declare(strict_types=1);

namespace App\ContactMessage\Application\Query;

interface ListContactMessageQueryInterface
{
    /**
     * @return array<ContactMessageView>
     */
    public function list(): array;
}
