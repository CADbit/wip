<?php

declare(strict_types=1);

namespace App\Reservation\Application\CreateReservation;

use App\Reservation\Domain\ValueObject\DateTimeRange;
use App\Resource\Domain\Entity\Resource;
use Symfony\Component\Uid\Uuid;

class CreateReservationCommand
{
    public function __construct(
        public Uuid $id,
        public Resource $resource,
        public string $reservedBy,
        public DateTimeRange $period,
    ) { }
}

