<?php

declare(strict_types=1);

namespace App\Reservation\Application\CreateReservation;

use App\Reservation\Domain\Entity\Reservation;
use App\Reservation\Domain\Repository\ReservationRepositoryInterface;
use Exception;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Handler komendy tworzenia rezerwacji.
 *
 * Obsługuje komendę CreateReservationCommand, tworząc nową rezerwację
 * w systemie i publikując zdarzenia domenowe związane z utworzeniem rezerwacji.
 */
#[AsMessageHandler]
class CreateReservationCommandHandler
{
    /**
     * @param ReservationRepositoryInterface $reservationRepository Repozytorium do zapisu rezerwacji
     * @param MessageBusInterface $eventBus Bus zdarzeń do publikowania zdarzeń domenowych
     */
    public function __construct(
        private readonly ReservationRepositoryInterface $reservationRepository,
        private readonly MessageBusInterface $eventBus
    ) {
    }

    /**
     * Przetwarza komendę tworzenia rezerwacji.
     *
     * Metoda:
     * 1. Tworzy nową encję Reservation na podstawie danych z komendy
     * 2. Zapisuje rezerwację w repozytorium
     * 3. Publikuje zdarzenia domenowe (np. ReservationCreated) przez event bus
     *
     * @param CreateReservationCommand $command Komenda zawierająca dane rezerwacji
     *
     * @return void
     *
     * @throws Exception Jeśli zapis do bazy danych się nie powiedzie
     */
    public function __invoke(CreateReservationCommand $command): void
    {
        $reservation = new Reservation(
            id: $command->id,
            resource: $command->resource,
            reservedBy: $command->reservedBy,
            period: $command->period
        );

        $this->reservationRepository->save($reservation);
        $this->reservationRepository->flush();

        $domainEvents = $reservation->pullDomainEvents();
        foreach ($domainEvents as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
