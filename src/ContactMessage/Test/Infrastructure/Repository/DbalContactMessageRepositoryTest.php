<?php
declare(strict_types=1);

namespace App\ContactMessage\Test\Infrastructure\Repository;

use App\ContactMessage\Infrastructure\Repository\DbalContactMessageRepository;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;

final class DbalContactMessageRepositoryTest extends TestCase
{
    public function testCreatePersistsRow(): void
    {
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);

        // Create table schema for the repository
        $schema = new Schema();
        $table = $schema->createTable('contact_messages');
        $table->addColumn('id', 'string');
        $table->addColumn('full_name', 'string');
        $table->addColumn('email', 'string');
        $table->addColumn('message', 'text');
        $table->addColumn('consent', 'boolean');
        $table->addColumn('created_at', 'string');

        foreach ($schema->toSql($connection->getDatabasePlatform()) as $sql) {
            $connection->executeStatement($sql);
        }

        $repo = new DbalContactMessageRepository($connection);

        $repo->create(
            'uuid-xyz',
            'Jan',
            'jan@example.com',
            'Hello',
            true,
            '2025-01-01T00:00:00+00:00'
        );

        $row = $connection->fetchAssociative('SELECT * FROM contact_messages WHERE id = :id', ['id' => 'uuid-xyz']);
        self::assertIsArray($row);
        self::assertSame('uuid-xyz', $row['id']);
        self::assertSame('Jan', $row['full_name']);
        self::assertSame('jan@example.com', $row['email']);
        self::assertSame('Hello', $row['message']);
        self::assertSame(1, (int) $row['consent']);
        self::assertSame('2025-01-01T00:00:00+00:00', $row['created_at']);
    }
}
