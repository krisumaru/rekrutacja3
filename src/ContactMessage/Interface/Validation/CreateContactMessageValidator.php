<?php
declare(strict_types=1);

namespace App\ContactMessage\Interface\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateContactMessageValidator
{
    /** @var array<string, array<string>> */
    private array $violations = [];


    /** @var array<string, array<Constraint>> */
    private array $rules;

    public function __construct(private readonly ValidatorInterface $validator)
    {
        $this->rules = [
            'fullName' => [new NotBlank(), new Length(max: 255)],
            'email' => [new NotBlank(), new Email(), new Length(max: 255)],
            'message' => [new NotBlank(), new Length(max: 5000)],
            'consent' => [new Type('boolean'), new IsTrue()],
        ];
    }

    /**
     * @param array{
     *     fullName?: string|null,
     *     email?: string|null,
     *     message?: string|null,
     *     consent?: bool|null,
     * } $data
     */
    public function isValid(array $data): bool
    {
        $extraKeys = array_diff_key($data, $this->rules);
        if (count($extraKeys) > 0) {
            $this->violations['extraKeys'] = ['Unsupported keys provided: ' . join(',', array_keys($extraKeys))];
            return false;
        }
        foreach ($this->rules as $property => $constraints) {
            $violations = $this->validator->validate($data[$property] ?? null, $constraints);
            if ($violations->count() > 0) {
                foreach ($violations as $violation) {
                    $this->violations[$property][] = (string) $violation->getMessage();
                }
            }
        }

        return count($this->violations) === 0;
    }

    /**
     * @return array<string, array<string>>
     */
    public function getViolations(): array
    {
        return $this->violations;
    }
}
