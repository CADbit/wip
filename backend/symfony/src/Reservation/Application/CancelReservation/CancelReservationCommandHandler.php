<?php

declare(strict_types=1);

namespace App\Reservation\Application\CancelReservation;

use App\Reservation\Domain\Repository\ReservationRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CancelReservationCommandHandler
{
    public function __construct(
        private readonly ReservationRepositoryInterface $reservationRepository
    ) {
    }

    public function __invoke(CancelReservationCommand $command): void
    {
        $reservation = $this->reservationRepository->findById($command->id->toString());

        if (!$reservation) {
            throw new \DomainException('Rezerwacja nie została znaleziona');
        }

        $this->reservationRepository->remove($reservation);
        $this->reservationRepository->flush();
    }
}

