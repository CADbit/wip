<?php

declare(strict_types=1);

namespace App\Reservation\Application\NotifyOnReservationCreated;

use App\Message\ReservationCreatedNotification;
use App\Reservation\Domain\Event\ReservationCreated;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class NotifyOnReservationCreatedHandler
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    public function __invoke(ReservationCreated $event): void
    {
        $notification = new ReservationCreatedNotification(
            reservationId: $event->reservationId,
            resourceId: $event->resource->id,
            resourceName: $event->resource->name,
            reservedBy: $event->reservedBy,
            startDate: $event->period->start()->format('Y-m-d H:i:s'),
            endDate: $event->period->end()->format('Y-m-d H:i:s'),
            createdAt: $event->createdAt->format('Y-m-d H:i:s')
        );

        $this->messageBus->dispatch($notification);
    }
}
