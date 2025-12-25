<?php

declare(strict_types=1);

namespace App\ContactMessage\Application\Repository;

interface ContactMessageRepositoryInterface
{
    public function create(string $id, string $fullName, string $email, string $message, bool $consentApproved, string $createdAt): void;
}
