<?php
declare(strict_types=1);


namespace App\ContactMessage\Application\Command;

use App\ContactMessage\Application\Repository\ContactMessageRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateContactMessageCommandHandler
{
    public function __construct(
        private readonly ContactMessageRepositoryInterface $repository,
    ) {
    }

    public function __invoke(CreateContactMessageCommand $command): void
    {
        $this->repository->create($command->id, $command->fullName, $command->email, $command->message, $command->consentApproved, $command->createdAt);
    }
}
