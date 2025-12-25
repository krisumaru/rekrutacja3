<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;

class ContactMessage
{
    private ?int $id = null;

    #[Assert\NotBlank(message: 'Imię i nazwisko jest wymagane.')]
    private string $fullName;

    #[Assert\NotBlank(message: 'Adres e-mail jest wymagany.')]
    #[Assert\Email(message: 'Podaj poprawny adres e-mail.')]
    private string $email;

    #[Assert\NotBlank(message: 'Treść wiadomości jest wymagana.')]
    private string $message;

    #[Assert\IsTrue(message: 'Zgoda na przetwarzanie danych osobowych jest wymagana.')]
    private bool $consent;

    private \DateTimeImmutable $createdAt;

    public function __construct(string $fullName, string $email, string $message, bool $consent)
    {
        $this->fullName = $fullName;
        $this->email = $email;
        $this->message = $message;
        $this->consent = $consent;
        $this->createdAt = new \DateTimeImmutable('now');
    }

    public function getId(): ?int { return $this->id; }
    public function getFullName(): string { return $this->fullName; }
    public function getEmail(): string { return $this->email; }
    public function getMessage(): string { return $this->message; }
    public function hasConsent(): bool { return $this->consent; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
