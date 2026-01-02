<?php

declare(strict_types=1);

namespace App\Reservation\Application\GetReservationsByResource;

use Symfony\Component\Uid\Uuid;

class GetReservationsByResourceQuery
{
    public function __construct(
        public Uuid $resourceId,
    ) {
    }
}

