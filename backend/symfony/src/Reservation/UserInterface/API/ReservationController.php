<?php

declare(strict_types=1);

namespace App\Reservation\UserInterface\API;

use App\Reservation\Application\CreateReservation\CreateReservationCommand;
use App\Reservation\Domain\Entity\Reservation;
use App\Reservation\Domain\Repository\ReservationRepositoryInterface;
use App\Reservation\Domain\ValueObject\DateTimeRange;
use App\Resource\Domain\Repository\ResourceRepositoryInterface;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/reservations', name: 'api_reservations_')]
class ReservationController extends AbstractController
{
    public function __construct(
        private readonly ReservationRepositoryInterface $reservationRepository,
        private readonly ResourceRepositoryInterface $resourceRepository,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        // Walidacja wymaganych pól
        $requiredFields = ['resourceId', 'reservedBy', 'startDate', 'endDate'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return new JsonResponse(['error' => "Missing required field: $field"], Response::HTTP_BAD_REQUEST);
            }
        }

        // Walidacja UUID zasobu
        try {
            $resourceUuid = Uuid::fromString($data['resourceId']);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => 'Invalid resource UUID format'], Response::HTTP_BAD_REQUEST);
        }

        // Sprawdzenie czy zasób istnieje
        $resource = $this->resourceRepository->findById($resourceUuid->toString());
        if (!$resource) {
            return new JsonResponse(['error' => 'Resource not found'], Response::HTTP_NOT_FOUND);
        }

        // Walidacja dat
        try {
            $startDate = new DateTimeImmutable($data['startDate']);
            $endDate = new DateTimeImmutable($data['endDate']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Invalid date format'], Response::HTTP_BAD_REQUEST);
        }

        // Utworzenie DateTimeRange
        try {
            $period = new DateTimeRange($startDate, $endDate);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Invalid date range: end date must be after start date'], Response::HTTP_BAD_REQUEST);
        }

        $command = new CreateReservationCommand(
            id: Uuid::v4(),
            resource: $resource,
            reservedBy: $data['reservedBy'],
            period: $period
        );

        $this->messageBus->dispatch($command);

        return new JsonResponse([
            'message' => 'Reservation created successfully',
            'data' => [
                'id' => $command->id->toString(),
                'resourceId' => $resource->id->toString(),
                'reservedBy' => $command->reservedBy,
                'startDate' => $period->start()->format('Y-m-d H:i:s'),
                'endDate' => $period->end()->format('Y-m-d H:i:s'),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        try {
            $uuid = Uuid::fromString($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => 'Invalid UUID format'], Response::HTTP_BAD_REQUEST);
        }

        $reservation = $this->reservationRepository->findById($uuid->toString());

        if (!$reservation) {
            return new JsonResponse(['error' => 'Reservation not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $this->serializeReservation($reservation)]);
    }

    #[Route('/{id}', name: 'cancel', methods: ['DELETE'])]
    public function cancel(string $id): JsonResponse
    {
        try {
            $uuid = Uuid::fromString($id);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => 'Invalid UUID format'], Response::HTTP_BAD_REQUEST);
        }

        $reservation = $this->reservationRepository->findById($uuid->toString());

        if (!$reservation) {
            return new JsonResponse(['error' => 'Reservation not found'], Response::HTTP_NOT_FOUND);
        }

        $this->reservationRepository->remove($reservation);
        $this->reservationRepository->flush();

        return new JsonResponse(['message' => 'Reservation cancelled successfully'], Response::HTTP_OK);
    }

    #[Route('/resource/{resourceId}', name: 'list_by_resource', methods: ['GET'])]
    public function listByResource(string $resourceId): JsonResponse
    {
        try {
            $resourceUuid = Uuid::fromString($resourceId);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => 'Invalid resource UUID format'], Response::HTTP_BAD_REQUEST);
        }

        // Sprawdzenie czy zasób istnieje
        $resource = $this->resourceRepository->findById($resourceUuid->toString());
        if (!$resource) {
            return new JsonResponse(['error' => 'Resource not found'], Response::HTTP_NOT_FOUND);
        }

        $reservations = $this->reservationRepository->findByResourceId($resourceUuid->toString());

        return new JsonResponse([
            'data' => array_map(fn(Reservation $reservation) => $this->serializeReservation($reservation), $reservations),
            'count' => count($reservations),
        ]);
    }

    private function serializeReservation(Reservation $reservation): array
    {
        return [
            'id' => $reservation->id->toString(),
            'resourceId' => $reservation->resource->id->toString(),
            'resourceName' => $reservation->resource->name,
            'reservedBy' => $reservation->reservedBy,
            'startDate' => $reservation->period->start()->format('Y-m-d H:i:s'),
            'endDate' => $reservation->period->end()->format('Y-m-d H:i:s'),
            'createdAt' => $reservation->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}

