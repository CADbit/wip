<?php

declare(strict_types=1);

namespace App\Reservation\Application\GetReservationsByResourceAndDate;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

class GetReservationsByResourceAndDateQuery
{
    public function __construct(
        public Uuid $resourceId,
        public DateTimeImmutable $date,
    ) {
    }
}
