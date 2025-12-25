<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContactMessageController extends AbstractController
{
    #[Route('/contact-messages', name: 'contact_message_create', methods: ['POST'])]
    public function create(Request $request, ValidatorInterface $validator, Connection $conn): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json([
                'errors' => ['body' => ['Invalid JSON body']]
            ], Response::HTTP_BAD_REQUEST);
        }

        $fullName = $data['fullName'] ?? $data['name'] ?? null;
        $email = $data['email'] ?? null;
        $message = $data['message'] ?? $data['content'] ?? null;
        $consent = $data['consent'] ?? $data['rodoConsent'] ?? null;

        // Normalize consent to bool if provided as string
        if (is_string($consent)) {
            $consent = in_array(strtolower($consent), ['1', 'true', 'yes', 'on'], true);
        }

        try {
            $entity = new ContactMessage((string)$fullName, (string)$email, (string)$message, (bool)$consent);
        } catch (\TypeError $e) {
            // If any nulls are cast leading to TypeError, handle with validation below
            $entity = new ContactMessage($fullName ?? '', $email ?? '', $message ?? '', (bool)$consent);
        }

        $violations = $validator->validate($entity);
        if (count($violations) > 0) {
            $errors = [];
            /** @var ConstraintViolation $violation */
            foreach ($violations as $violation) {
                $property = $violation->getPropertyPath();
                $errors[$property][] = $violation->getMessage();
            }

            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        // Prepare values
        $createdAt = new \DateTimeImmutable('now');

        // Insert using DBAL. Use RETURNING on PostgreSQL, otherwise use lastInsertId (e.g., SQLite in tests)
        $platform = $conn->getDatabasePlatform();
        $id = null;

        if ($platform instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform) {
            $id = (int) $conn->fetchOne(
                'INSERT INTO contact_messages (full_name, email, message, consent, created_at) VALUES (:full_name, :email, :message, :consent, :created_at) RETURNING id',
                [
                    'full_name' => $entity->getFullName(),
                    'email' => $entity->getEmail(),
                    'message' => $entity->getMessage(),
                    'consent' => $entity->hasConsent(),
                    'created_at' => $createdAt->format('Y-m-d H:i:s'),
                ],
                [
                    'consent' => \PDO::PARAM_BOOL,
                ]
            );
        } else {
            $conn->insert('contact_messages', [
                'full_name' => $entity->getFullName(),
                'email' => $entity->getEmail(),
                'message' => $entity->getMessage(),
                'consent' => $entity->hasConsent() ? 1 : 0,
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
            ]);
            // lastInsertId works for SQLite
            $id = (int) $conn->lastInsertId();
        }

        return $this->json([
            'id' => $id,
            'fullName' => $entity->getFullName(),
            'email' => $entity->getEmail(),
            'message' => $entity->getMessage(),
            'consent' => $entity->hasConsent(),
            'createdAt' => $createdAt->format(DATE_ATOM),
        ], Response::HTTP_CREATED);
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
}
