<?php

namespace App\ContactMessage\Interface\Http;

use App\ContactMessage\Application\Command\CreateContactMessageCommand;
use App\ContactMessage\Application\Query\ListContactMessageQueryInterface;
use App\ContactMessage\Application\Query\ViewInterface;
use App\ContactMessage\Interface\Sanitizer\SanitizerInterface;
use App\ContactMessage\Interface\Validation\CreateContactMessageValidator;
use DateTimeInterface;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
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
        private readonly LoggerInterface $logger,
        private readonly SanitizerInterface $sanitizer,
    ) {
    }

    #[Route('/contact-messages', name: 'contact_message_create', methods: ['POST'])]
    public function create(Request $request, CreateContactMessageValidator $validator): Response
    {
        $contentType = $request->headers->get('Content-Type');
        if ($contentType !== 'application/json') {
            return $this->respondBadRequest(['contentType' => ['Invalid Content-Type']]);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->respondBadRequest(['body' => ['Invalid JSON']]);
        }
        $data = $this->sanitizer->sanitizeAll($data);

        /**
         * @var array{fullName: string, email: string, message: string, consent: bool} $data
         */
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
                $this->logger->error($e->getMessage());
                return $this->respondInternalServerError();
            }

            return $this->respondCreated();

        } else {
            return $this->respondBadRequest($validator->getViolations());
        }
    }

    #[Route('/contact-messages', name: 'contact_message_list', methods: ['GET'])]
    public function list(Request $request, ListContactMessageQueryInterface $query): JsonResponse
    {
        $contentType = $request->headers->get('Content-Type');
        if ($contentType !== 'application/json') {
            return $this->respondBadRequest(['contentType' => ['Invalid Content-Type']]);
        }
        $accept = $request->headers->get('Accept');
        if ($accept !== 'application/json') {
            return $this->respondBadRequest(['accept' => ['Invalid Accept']]);
        }

        return $this->jsonListResponse($query->list());
    }

    /**
     * @param array<string, array<string>> $violations
     */
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

    /**
     * @param array<string, ViewInterface> $list
     */
    private function jsonListResponse(array $list): JsonResponse
    {
        return new JsonResponse(
            array_map(static function (ViewInterface $item) {
                return $item->toPrimitiveArray();
            }, $list),
            Response::HTTP_OK,
        );
    }
}
