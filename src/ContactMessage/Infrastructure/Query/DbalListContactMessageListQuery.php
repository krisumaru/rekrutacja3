<?php

declare(strict_types=1);

namespace App\ContactMessage\Infrastructure\Query;

use App\ContactMessage\Application\Query\ContactMessageView;
use App\ContactMessage\Application\Query\ListContactMessageQueryInterface;
use Doctrine\DBAL\Connection;

final readonly class DbalListContactMessageListQuery implements ListContactMessageQueryInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function list(): array
    {
        /** @var array<array{
         *     id: string,
         *     full_name: string,
         *     email: string,
         *     message: string,
         *     created_at: string,
         *     consent: bool,
         * }> $rows
         */
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, full_name, email, message, created_at FROM contact_messages ORDER BY id DESC',
        );

        return array_map(static function ($row) {
            return ContactMessageView::fromArray($row);
        }, $rows);
    }
}
