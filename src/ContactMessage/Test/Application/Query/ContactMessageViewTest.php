<?php
declare(strict_types=1);

namespace App\ContactMessage\Test\Application\Query;

use App\ContactMessage\Application\Query\ContactMessageView;
use PHPUnit\Framework\TestCase;

final class ContactMessageViewTest extends TestCase
{
    public function testFromArrayBuildsViewCorrectly(): void
    {
        $data = [
            'id' => '019b56eb-c530-735a-8dfe-42cd78d0fed6',
            'full_name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'message' => 'Hello world',
            'consent' => true,
            'created_at' => '2025-01-01T12:34:56+00:00',
        ];

        $view = ContactMessageView::fromArray($data);

        self::assertSame($data['id'], $view->id);
        self::assertSame($data['full_name'], $view->fullName);
        self::assertSame($data['email'], $view->email);
        self::assertSame($data['message'], $view->message);
        self::assertTrue($view->consentApproved);
        self::assertSame($data['created_at'], $view->createdAt->format(DATE_RFC3339));
    }

    public function testToPrimitiveArrayMatchesExpectedShapeAndValues(): void
    {
        $view = ContactMessageView::fromArray([
            'id' => '019b56eb-c530-735a-8dfe-42cd78d0fed6',
            'full_name' => 'Jan',
            'email' => 'jan@example.com',
            'message' => 'Hi',
            'consent' => false,
            'created_at' => '2025-01-01T00:00:00+00:00',
        ]);

        $array = $view->toPrimitiveArray();

        // Assert keys and values
        self::assertSame('019b56eb-c530-735a-8dfe-42cd78d0fed6', $array['id']);
        self::assertSame('Jan', $array['fullName']);
        self::assertSame('jan@example.com', $array['email']);
        self::assertSame('Hi', $array['message']);
        self::assertArrayHasKey('consentApproved', $array);
        self::assertFalse($array['consentApproved']);
        self::assertSame('2025-01-01T00:00:00+00:00', $array['createdAt']);
    }
}
