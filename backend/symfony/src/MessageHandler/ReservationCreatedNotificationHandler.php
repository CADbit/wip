<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\ReservationCreatedNotification;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ReservationCreatedNotificationHandler
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     *  Tutaj można dodać dodatkową logikę, np.:
     *  - Wysyłanie emaili
     *  - Wysyłanie powiadomień push
     *  - Integracja z zewnętrznymi systemami
     *  - Aktualizacja cache
     *  - etc.
     */
    public function __invoke(ReservationCreatedNotification $notification): void
    {
        $this->logger->info('Nowa rezerwacja utworzona', [
            'reservationId' => $notification->reservationId->toString(),
            'resourceId' => $notification->resourceId->toString(),
            'resourceName' => $notification->resourceName,
            'reservedBy' => $notification->reservedBy,
            'startDate' => $notification->startDate,
            'endDate' => $notification->endDate,
            'createdAt' => $notification->createdAt,
        ]);

        // TODO: Tutaj wykonujemy akcje.
    }
}
