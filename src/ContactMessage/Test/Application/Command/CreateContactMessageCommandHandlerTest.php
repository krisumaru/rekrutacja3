<?php
declare(strict_types=1);

namespace App\ContactMessage\Test\Application\Command;

use App\ContactMessage\Application\Command\CreateContactMessageCommand;
use App\ContactMessage\Application\Command\CreateContactMessageCommandHandler;
use App\ContactMessage\Application\Repository\ContactMessageRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateContactMessageCommandHandlerTest extends TestCase
{
    private ContactMessageRepositoryInterface&MockObject $repo;

    protected function setUp(): void
    {
        $this->repo = $this->createMock(ContactMessageRepositoryInterface::class);
    }

    public function testItPersistsUsingRepository(): void
    {
        $handler = new CreateContactMessageCommandHandler($this->repo);
        $cmd = new CreateContactMessageCommand(
            'uuid-1', 'Jan', 'jan@example.com', 'Hi', true, '2025-01-01T00:00:00+00:00'
        );

        $this->repo
            ->expects(self::once())
            ->method('create')
            ->with(
                'uuid-1', 'Jan', 'jan@example.com', 'Hi', true, '2025-01-01T00:00:00+00:00'
            );

        $handler($cmd);
    }
}
