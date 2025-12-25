<?php
declare(strict_types=1);

namespace App\ContactMessage\Test\Interface\Validation;

use App\ContactMessage\Interface\Validation\CreateContactMessageValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class CreateContactMessageValidatorTest extends TestCase
{
    private CreateContactMessageValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new CreateContactMessageValidator(Validation::createValidator());
    }

    public function testValidPayloadPasses(): void
    {
        $data = [
            'fullName' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'message' => 'Hello',
            'consent' => true,
        ];

        self::assertTrue($this->validator->isValid($data));
        self::assertSame([], $this->validator->getViolations());
    }

    public function testMissingAndInvalidFieldsProduceViolations(): void
    {
        $data = [
            'fullName' => '',
            'email' => 'not-an-email',
            'message' => '',
            'consent' => false,
        ];

        self::assertFalse($this->validator->isValid($data));
        $violations = $this->validator->getViolations();

        self::assertArrayHasKey('fullName', $violations);
        self::assertArrayHasKey('email', $violations);
        self::assertArrayHasKey('message', $violations);
        self::assertArrayHasKey('consent', $violations);
        self::assertNotEmpty($violations['fullName']);
        self::assertNotEmpty($violations['email']);
        self::assertNotEmpty($violations['message']);
        self::assertNotEmpty($violations['consent']);
    }

    public function testExtraKeysAreReported(): void
    {
        $data = [
            'fullName' => 'Jan',
            'email' => 'jan@example.com',
            'message' => 'Hi',
            'consent' => true,
            'unexpected' => 'value',
        ];

        self::assertFalse($this->validator->isValid($data));
        $violations = $this->validator->getViolations();
        self::assertArrayHasKey('extraKeys', $violations);
        self::assertNotEmpty($violations['extraKeys']);
    }
}
