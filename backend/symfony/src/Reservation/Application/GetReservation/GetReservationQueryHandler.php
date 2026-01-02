<?php

declare(strict_types=1);

namespace App\Reservation\Application\GetReservation;

use App\Reservation\Domain\Entity\Reservation;
use App\Reservation\Domain\Repository\ReservationRepositoryInterface;

class GetReservationQueryHandler
{
    public function __construct(
        private readonly ReservationRepositoryInterface $reservationRepository
    ) {
    }

    public function __invoke(GetReservationQuery $query): ?Reservation
    {
        return $this->reservationRepository->findById($query->id->toString());
    }
}
