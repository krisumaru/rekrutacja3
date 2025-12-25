<?php

declare(strict_types=1);

namespace App\ContactMessage\Application\Query;

use DateTimeImmutable;
use DateTimeInterface;

final readonly class ContactMessageView implements ViewInterface
{
    /**
     * @param array{
     *     id: string,
     *     full_name: string,
     *     email: string,
     *     message: string,
     *     consent: bool,
     *     created_at: string,
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            fullName: $data['full_name'],
            email: $data['email'],
            message: $data['message'],
            consentApproved: $data['consent'],
            createdAt: new DateTimeImmutable($data['created_at']),
        );
    }

    public function __construct(
        public string $id,
        public string $fullName,
        public string $email,
        public string $message,
        public bool $consentApproved,
        public DateTimeImmutable $createdAt,
    ) {
    }

    public function toPrimitiveArray(): array
    {
        return [
            'id' => $this->id,
            'fullName' => $this->fullName,
            'email' => $this->email,
            'message' => $this->message,
            'consentApproved' => $this->consentApproved,
            'createdAt' => $this->createdAt->format(DateTimeInterface::RFC3339),
        ];
    }
}
