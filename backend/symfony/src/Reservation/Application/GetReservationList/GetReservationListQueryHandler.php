<?php

declare(strict_types=1);

namespace App\Reservation\Application\GetReservationList;

use App\Reservation\Domain\Entity\Reservation;
use App\Reservation\Domain\Repository\ReservationRepositoryInterface;

class GetReservationListQueryHandler
{
    public function __construct(
        private readonly ReservationRepositoryInterface $reservationRepository
    ) {
    }

    /**
     * @return Reservation[]
     */
    public function __invoke(GetReservationListQuery $query): array
    {
        return $this->reservationRepository->findAll();
    }
}

