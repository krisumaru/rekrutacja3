<?php
declare(strict_types=1);

namespace App\ContactMessage\Application\Command;

class CreateContactMessageCommand
{
    public function __construct(
        public string $id,
        public string $fullName,
        public string $email,
        public string $message,
        public bool $consentApproved,
        public string $createdAt,
    ) {
    }
}
