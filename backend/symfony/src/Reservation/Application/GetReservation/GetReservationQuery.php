<?php

declare(strict_types=1);

namespace App\Reservation\Application\GetReservation;

use Symfony\Component\Uid\Uuid;

class GetReservationQuery
{
    public function __construct(
        public Uuid $id,
    ) {
    }
}
