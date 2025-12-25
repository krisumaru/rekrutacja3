<?php

declare(strict_types=1);

namespace App\ContactMessage\Infrastructure\Repository;

use App\ContactMessage\Application\Repository\ContactMessageRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

final readonly class DbalContactMessageRepository implements ContactMessageRepositoryInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function create(string $id, string $fullName, string $email, string $message, bool $consentApproved, string $createdAt): void
    {
        $this->connection->executeStatement(
            'INSERT INTO contact_messages (id, full_name, email, message, consent, created_at) 
                VALUES (:id, :full_name, :email, :message, :consent, :created_at)',
            [
                'id' => $id,
                'full_name' => $fullName,
                'email' => $email,
                'message' => $message,
                'consent' => $consentApproved,
                'created_at' => $createdAt,
            ],
            [
                'consent' => ParameterType::BOOLEAN,
            ],
        );
    }
}
