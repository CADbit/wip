<?php

declare(strict_types=1);

namespace App\Reservation\Application\GetReservationsByResource;

use App\Reservation\Domain\Entity\Reservation;
use App\Reservation\Domain\Repository\ReservationRepositoryInterface;

class GetReservationsByResourceQueryHandler
{
    public function __construct(
        private readonly ReservationRepositoryInterface $reservationRepository
    ) {
    }

    /**
     * @return Reservation[]
     */
    public function __invoke(GetReservationsByResourceQuery $query): array
    {
        return $this->reservationRepository->findByResourceId($query->resourceId->toString());
    }
}

