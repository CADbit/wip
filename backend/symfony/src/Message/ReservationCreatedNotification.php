<?php

declare(strict_types=1);

namespace App\Message;

use Symfony\Component\Uid\Uuid;

class ReservationCreatedNotification
{
    public function __construct(
        public readonly Uuid $reservationId,
        public readonly Uuid $resourceId,
        public readonly string $resourceName,
        public readonly string $reservedBy,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly string $createdAt
    ) {
    }
}
