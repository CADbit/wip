<?php

declare(strict_types=1);

namespace App\Reservation\Domain\Event;

use App\Reservation\Domain\ValueObject\DateTimeRange;
use App\Resource\Domain\Entity\Resource;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

class ReservationCreated
{
    public function __construct(
        public readonly Uuid $reservationId,
        public readonly Resource $resource,
        public readonly string $reservedBy,
        public readonly DateTimeRange $period,
        public readonly DateTimeImmutable $createdAt
    ) {
    }
}
