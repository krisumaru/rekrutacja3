<?php

namespace App\ContactMessage\Interface\Http;

use App\ContactMessage\Application\Command\CreateContactMessageCommand;
use App\ContactMessage\Interface\Validation\CreateContactMessageValidator;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Psr\Clock\ClockInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

class ContactMessageController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly ClockInterface $clock,
    ) {
    }

    #[Route('/contact-messages', name: 'contact_message_create', methods: ['POST'])]
    public function create(Request $request, CreateContactMessageValidator $validator): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->respondBadRequest(['body' => ['Invalid JSON']]);
        }

        if ($validator->isValid($data)) {
            try {
                $this->bus->dispatch(new CreateContactMessageCommand(
                    Uuid::uuid7($this->clock->now())->toString(),
                    $data['fullName'],
                    $data['email'],
                    $data['message'],
                    (bool) $data['consent'],
                    $this->clock->now()->format(DateTimeInterface::RFC3339),
                ));
            } catch (Throwable $e) {
                // log exception
                return $this->respondInternalServerError();
            }

            return $this->respondCreated();

        } else {
            return $this->respondBadRequest($validator->getViolations());
        }
    }

    #[Route('/contact-messages', name: 'contact_message_list', methods: ['GET'])]
    public function list(Connection $conn): JsonResponse
    {
        $rows = $conn->fetchAllAssociative('SELECT id, full_name, email, message, created_at FROM contact_messages ORDER BY id DESC');
        $data = array_map(static function (array $row) {
            $createdAt = new \DateTimeImmutable(is_string($row['created_at']) ? $row['created_at'] : 'now');
            return [
                'id' => (int) $row['id'],
                'fullName' => $row['full_name'],
                'email' => $row['email'],
                'message' => $row['message'],
                'createdAt' => $createdAt->format(DATE_ATOM),
            ];
        }, $rows);

        return new JsonResponse($data);
    }

    public function respondBadRequest(array $violations): JsonResponse
    {
        return $this->json(['errors' => $violations], Response::HTTP_BAD_REQUEST);
    }

    public function respondCreated(): Response
    {
        return new Response('', Response::HTTP_CREATED);
    }

    public function respondInternalServerError(): Response
    {
        return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
