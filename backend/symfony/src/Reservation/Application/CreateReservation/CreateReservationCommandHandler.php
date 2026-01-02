<?php

declare(strict_types=1);

namespace App\Reservation\Application\CreateReservation;

use App\Reservation\Domain\Entity\Reservation;
use App\Reservation\Domain\Repository\ReservationRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateReservationCommandHandler
{
    public function __construct(
        private readonly ReservationRepositoryInterface $reservationRepository
    ) {
    }

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
    }
}

