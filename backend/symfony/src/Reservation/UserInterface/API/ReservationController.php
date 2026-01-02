<?php

declare(strict_types=1);

namespace App\Reservation\UserInterface\API;

use App\Reservation\Application\CreateReservation\CreateReservationCommand;
use App\Reservation\Domain\Entity\Reservation;
use App\Reservation\Domain\Repository\ReservationRepositoryInterface;
use App\Reservation\Domain\ValueObject\DateTimeRange;
use App\Resource\Domain\Repository\ResourceRepositoryInterface;
use App\UserInterface\API\ApiResponseHelper;
use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
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
            return ApiResponseHelper::error('Invalid JSON', [], Response::HTTP_BAD_REQUEST);
        }

        // Walidacja wymaganych pól
        $requiredFields = ['resourceId', 'reservedBy', 'startDate', 'endDate'];
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $missingFields[$field] = "Pole '$field' jest wymagane";
            }
        }

        if (!empty($missingFields)) {
            return ApiResponseHelper::validationError('Brakuje wymaganych pól', $missingFields);
        }

        // Walidacja UUID zasobu
        try {
            $resourceUuid = Uuid::fromString($data['resourceId']);
        } catch (\InvalidArgumentException $e) {
            return ApiResponseHelper::validationError('Nieprawidłowy format UUID zasobu', [
                'resourceId' => 'Nieprawidłowy format UUID'
            ]);
        }

        // Sprawdzenie czy zasób istnieje
        $resource = $this->resourceRepository->findById($resourceUuid->toString());
        if (!$resource) {
            return ApiResponseHelper::error('Zasób nie został znaleziony', [], Response::HTTP_NOT_FOUND);
        }

        // Walidacja dat
        try {
            $startDate = new DateTimeImmutable($data['startDate']);
            $endDate = new DateTimeImmutable($data['endDate']);
        } catch (\Exception $e) {
            return ApiResponseHelper::validationError('Nieprawidłowy format daty', [
                'startDate' => 'Nieprawidłowy format daty rozpoczęcia',
                'endDate' => 'Nieprawidłowy format daty zakończenia'
            ]);
        }

        // Utworzenie DateTimeRange
        try {
            $period = new DateTimeRange($startDate, $endDate);
        } catch (\Exception $e) {
            return ApiResponseHelper::validationError('Nieprawidłowy zakres dat', [
                'endDate' => 'Data zakończenia musi być późniejsza niż data rozpoczęcia'
            ]);
        }

        // Sprawdzenie konfliktów z istniejącymi rezerwacjami
        $existingReservations = $this->reservationRepository->findByResourceId($resourceUuid->toString());
        $conflicts = [];
        foreach ($existingReservations as $existingReservation) {
            if ($period->overlaps($existingReservation->period)) {
                $conflicts[] = [
                    'startDate' => $existingReservation->period->start()->format('Y-m-d H:i:s'),
                    'endDate' => $existingReservation->period->end()->format('Y-m-d H:i:s'),
                    'reservedBy' => $existingReservation->reservedBy,
                ];
            }
        }

        if (!empty($conflicts)) {
            return ApiResponseHelper::validationError(
                'Wybrany termin koliduje z istniejącymi rezerwacjami',
                [
                    'startDate' => 'Termin koliduje z istniejącymi rezerwacjami',
                    'endDate' => 'Termin koliduje z istniejącymi rezerwacjami',
                    'conflicts' => $conflicts
                ]
            );
        }

        $command = new CreateReservationCommand(
            id: Uuid::v4(),
            resource: $resource,
            reservedBy: $data['reservedBy'],
            period: $period
        );

        $this->messageBus->dispatch($command);

        return ApiResponseHelper::success([
            'id' => $command->id->toString(),
            'resourceId' => $resource->id->toString(),
            'reservedBy' => $command->reservedBy,
            'startDate' => $period->start()->format('Y-m-d H:i:s'),
            'endDate' => $period->end()->format('Y-m-d H:i:s'),
        ], 'Rezerwacja została utworzona pomyślnie', Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        try {
            $uuid = Uuid::fromString($id);
        } catch (\InvalidArgumentException $e) {
            return ApiResponseHelper::validationError('Nieprawidłowy format UUID', [
                'id' => 'Nieprawidłowy format UUID'
            ]);
        }

        $reservation = $this->reservationRepository->findById($uuid->toString());

        if (!$reservation) {
            return ApiResponseHelper::error('Rezerwacja nie została znaleziona', [], Response::HTTP_NOT_FOUND);
        }

        return ApiResponseHelper::success($this->serializeReservation($reservation));
    }

    #[Route('/{id}', name: 'cancel', methods: ['DELETE'])]
    public function cancel(string $id): JsonResponse
    {
        try {
            $uuid = Uuid::fromString($id);
        } catch (\InvalidArgumentException $e) {
            return ApiResponseHelper::validationError('Nieprawidłowy format UUID', [
                'id' => 'Nieprawidłowy format UUID'
            ]);
        }

        $reservation = $this->reservationRepository->findById($uuid->toString());

        if (!$reservation) {
            return ApiResponseHelper::error('Rezerwacja nie została znaleziona', [], Response::HTTP_NOT_FOUND);
        }

        $this->reservationRepository->remove($reservation);
        $this->reservationRepository->flush();

        return ApiResponseHelper::success(null, 'Rezerwacja została anulowana pomyślnie');
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $reservations = $this->reservationRepository->findAll();

        return ApiResponseHelper::success(
            array_map(fn(Reservation $reservation) => $this->serializeReservation($reservation), $reservations)
        );
    }

    #[Route('/resource/{resourceId}', name: 'list_by_resource', methods: ['GET'])]
    public function listByResource(string $resourceId): JsonResponse
    {
        try {
            $resourceUuid = Uuid::fromString($resourceId);
        } catch (\InvalidArgumentException $e) {
            return ApiResponseHelper::validationError('Nieprawidłowy format UUID zasobu', [
                'resourceId' => 'Nieprawidłowy format UUID'
            ]);
        }

        // Sprawdzenie czy zasób istnieje
        $resource = $this->resourceRepository->findById($resourceUuid->toString());
        if (!$resource) {
            return ApiResponseHelper::error('Zasób nie został znaleziony', [], Response::HTTP_NOT_FOUND);
        }

        $reservations = $this->reservationRepository->findByResourceId($resourceUuid->toString());

        return ApiResponseHelper::success(
            array_map(fn(Reservation $reservation) => $this->serializeReservation($reservation), $reservations)
        );
    }

    #[Route('/resource/{resourceId}/date/{date}', name: 'list_by_resource_and_date', methods: ['GET'])]
    public function listByResourceAndDate(string $resourceId, string $date): JsonResponse
    {
        try {
            $resourceUuid = Uuid::fromString($resourceId);
        } catch (InvalidArgumentException $e) {
            return ApiResponseHelper::validationError('Nieprawidłowy format UUID zasobu', [
                'resourceId' => 'Nieprawidłowy format UUID'
            ]);
        }

        // Sprawdzenie czy zasób istnieje
        $resource = $this->resourceRepository->findById($resourceUuid->toString());
        if (!$resource) {
            return ApiResponseHelper::error('Zasób nie został znaleziony', [], Response::HTTP_NOT_FOUND);
        }

        // Walidacja daty
        try {
            $dateTime = new DateTimeImmutable($date);
        } catch (Exception $e) {
            return ApiResponseHelper::validationError('Nieprawidłowy format daty', [
                'date' => 'Nieprawidłowy format daty. Oczekiwany format: Y-m-d (np. 2024-01-15)'
            ]);
        }

        $reservations = $this->reservationRepository->findByResourceIdAndDate($resourceUuid->toString(), $dateTime);

        return ApiResponseHelper::success(
            array_map(fn(Reservation $reservation) => $this->serializeReservation($reservation), $reservations)
        );
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

