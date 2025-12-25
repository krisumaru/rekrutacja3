<?php
declare(strict_types=1);

namespace App\ContactMessage\Test\Interface\Http;

use App\ContactMessage\Application\Command\CreateContactMessageCommand;
use App\ContactMessage\Interface\Http\ContactMessageController;
use App\ContactMessage\Interface\Validation\CreateContactMessageValidator;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ContactMessageControllerTest extends TestCase
{
    /** @var MessageBusInterface&MockObject */
    private MessageBusInterface $bus;
    /** @var ClockInterface&MockObject */
    private ClockInterface $clock;

    protected function setUp(): void
    {
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->clock = $this->createMock(ClockInterface::class);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testCreateReturnsBadRequestOnInvalidJson(): void
    {
        $controller = new ContactMessageController($this->bus, $this->clock);
        // AbstractController::json() accesses the container; provide a dummy one
        $controller->setContainer($this->createMock(ContainerInterface::class));
        $validator = $this->createMock(CreateContactMessageValidator::class);

        $request = new Request(content: '{invalid-json}');
        $response = $controller->create($request, $validator);

        self::assertSame(400, $response->getStatusCode());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testCreateReturnsBadRequestWhenValidationFails(): void
    {
        $controller = new ContactMessageController($this->bus, $this->clock);
        $controller->setContainer($this->createMock(ContainerInterface::class));
        $validator = $this->createMock(CreateContactMessageValidator::class);
        $validator->method('isValid')->willReturn(false);
        $validator->method('getViolations')->willReturn(['fullName' => ['Not blank']]);

        $request = new Request(content: json_encode([
            'fullName' => '', 'email' => '', 'message' => '', 'consent' => false,
        ], JSON_THROW_ON_ERROR));

        $response = $controller->create($request, $validator);
        self::assertSame(400, $response->getStatusCode());
        self::assertStringContainsString('errors', (string) $response->getContent());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testCreateDispatchesCommandAndReturnsCreated(): void
    {
        $fixedNow = new DateTimeImmutable('2025-01-01T00:00:00+00:00');
        $this->clock->method('now')->willReturn($fixedNow);

        $controller = new ContactMessageController($this->bus, $this->clock);
        $controller->setContainer($this->createMock(ContainerInterface::class));
        $validator = $this->createMock(CreateContactMessageValidator::class);
        $validator->method('isValid')->willReturn(true);

        $this->bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function ($message) use ($fixedNow) {
                // Message might be wrapped in an Envelope depending on transport; handle both cases
                if ($message instanceof Envelope) {
                    $message = $message->getMessage();
                }
                self::assertInstanceOf(CreateContactMessageCommand::class, $message);
                self::assertSame('Jan', $message->fullName);
                self::assertSame('jan@example.com', $message->email);
                self::assertSame('Hello', $message->message);
                self::assertTrue($message->consentApproved);
                self::assertStringStartsWith($fixedNow->format('Y-m-d\TH:i'), $message->createdAt);
                self::assertNotEmpty($message->id); // UUID v7 generated
                return true;
            }))
            ->willReturn(new Envelope(new \stdClass()));

        $request = new Request(content: json_encode([
            'fullName' => 'Jan', 'email' => 'jan@example.com', 'message' => 'Hello', 'consent' => true,
        ], JSON_THROW_ON_ERROR));

        $response = $controller->create($request, $validator);
        self::assertSame(201, $response->getStatusCode());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testCreateReturnsInternalServerErrorWhenDispatchFails(): void
    {
        $fixedNow = new DateTimeImmutable('2025-01-01T00:00:00+00:00');
        $this->clock->method('now')->willReturn($fixedNow);

        $controller = new ContactMessageController($this->bus, $this->clock);
        $controller->setContainer($this->createMock(ContainerInterface::class));
        $validator = $this->createMock(CreateContactMessageValidator::class);
        $validator->method('isValid')->willReturn(true);

        $this->bus
            ->expects(self::once())
            ->method('dispatch')
            ->willThrowException(new \RuntimeException('fail'));

        $request = new Request(content: json_encode([
            'fullName' => 'Jan', 'email' => 'jan@example.com', 'message' => 'Hello', 'consent' => true,
        ], JSON_THROW_ON_ERROR));

        $response = $controller->create($request, $validator);
        self::assertSame(500, $response->getStatusCode());
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testListMapsRowsToExpectedJson(): void
    {
        $controller = new ContactMessageController($this->bus, $this->clock);
        $controller->setContainer($this->createMock(ContainerInterface::class));
        /** @var Connection&MockObject $conn */
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn([
            ['id' => 5, 'full_name' => 'Jan', 'email' => 'jan@example.com', 'message' => 'Hi', 'created_at' => '2025-01-01T00:00:00+00:00'],
        ]);

        $response = $controller->list($conn);
        self::assertSame(200, $response->getStatusCode());
        $data = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertCount(1, $data);
        self::assertSame(5, $data[0]['id']);
        self::assertSame('Jan', $data[0]['fullName']);
        self::assertSame('jan@example.com', $data[0]['email']);
        self::assertSame('Hi', $data[0]['message']);
        self::assertSame('2025-01-01T00:00:00+00:00', $data[0]['createdAt']);
    }
}
