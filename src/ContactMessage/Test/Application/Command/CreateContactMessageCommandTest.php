<?php
declare(strict_types=1);

namespace App\ContactMessage\Test\Application\Command;

use App\ContactMessage\Application\Command\CreateContactMessageCommand;
use PHPUnit\Framework\TestCase;

final class CreateContactMessageCommandTest extends TestCase
{
    public function testItHoldsProvidedData(): void
    {
        $cmd = new CreateContactMessageCommand(
            'uuid-123',
            'Jan Kowalski',
            'jan@example.com',
            'Hello',
            true,
            '2025-01-01T10:00:00+00:00',
        );

        self::assertSame('uuid-123', $cmd->id);
        self::assertSame('Jan Kowalski', $cmd->fullName);
        self::assertSame('jan@example.com', $cmd->email);
        self::assertSame('Hello', $cmd->message);
        self::assertTrue($cmd->consentApproved);
        self::assertSame('2025-01-01T10:00:00+00:00', $cmd->createdAt);
    }
}
