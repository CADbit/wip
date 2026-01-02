<?php

declare(strict_types=1);

namespace App\Reservation\Domain\Repository;

use App\Reservation\Domain\Entity\Reservation;

interface ReservationRepositoryInterface
{
    public function save(Reservation $Reservation): void;
    public function remove(Reservation $Reservation): void;
    public function flush(): void;
    public function findById(string $id): ?Reservation;

    /** @return Reservation[] */
    public function findAll(): array;

    /** @return Reservation[] */
    public function findByResourceId(string $resourceId): array;
}
