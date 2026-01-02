<?php

declare(strict_types=1);

namespace App\Reservation\Application\CancelReservation;

use Symfony\Component\Uid\Uuid;

class CancelReservationCommand
{
    public function __construct(
        public Uuid $id,
    ) {
    }
}
