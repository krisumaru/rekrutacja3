<?php
declare(strict_types=1);

namespace App\ContactMessage\Interface\Validation;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateContactMessageValidator
{
    private array $violations = [];
    private array $rules = [
        'fullName' => ['notBlank'],
        'email' => ['email'],
        'message' => ['notBlank'],
        'consent' => ['true'],
    ];

    public function __construct(private readonly ValidatorInterface $validator)
    {
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
            $this->violations['extraKeys'] = 'Unsupported keys provided: ' . join(',', array_keys($extraKeys));
            return false;
        }
        foreach ($this->rules as $property => $constraints) {
            $violations = $this->validator->validate($data[$property] ?? null, $constraints);
            if ($violations->count() > 0) {
                foreach ($violations as $violation) {
                    $this->violations[$property] = $violation->getMessage();
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
