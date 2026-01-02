<?php

declare(strict_types=1);

namespace App\Reservation\Application\GetReservationsByResourceAndDate;

use App\Reservation\Domain\Entity\Reservation;
use App\Reservation\Domain\Repository\ReservationRepositoryInterface;

class GetReservationsByResourceAndDateQueryHandler
{
    public function __construct(
        private readonly ReservationRepositoryInterface $reservationRepository
    ) {
    }

    /**
     * @return Reservation[]
     */
    public function __invoke(GetReservationsByResourceAndDateQuery $query): array
    {
        return $this->reservationRepository->findByResourceIdAndDate(
            $query->resourceId->toString(),
            $query->date
        );
    }
}

