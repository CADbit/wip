<?php

declare(strict_types=1);

namespace App\Reservation\Domain\Repository;

use App\Reservation\Domain\Entity\Reservation;
use DateTimeImmutable;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

/**
 * Interfejs repozytorium dla rezerwacji.
 *
 * Definiuje kontrakt dla operacji CRUD na rezerwacjach w systemie.
 * Implementacja powinna zapewniać trwałość danych w bazie danych.
 *
 * @see \App\Reservation\Infrastructure\Doctrine\ReservationRepository
 */
interface ReservationRepositoryInterface
{
    /**
     * Zapisuje rezerwację do repozytorium (bez zapisu do bazy).
     *
     * @param Reservation $reservation Rezerwacja do zapisania
     */
    public function save(Reservation $reservation): void;

    /**
     * Oznacza rezerwację do usunięcia (bez usunięcia z bazy).
     *
     * @param Reservation $reservation Rezerwacja do usunięcia
     */
    public function remove(Reservation $reservation): void;

    /**
     * Zapisuje wszystkie oczekujące zmiany do bazy danych.
     *
     * @throws ORMException Jeśli wystąpi błąd podczas zapisu
     * @throws OptimisticLockException Jeśli wystąpi konflikt wersji
     */
    public function flush(): void;

    /**
     * Znajduje rezerwację po jej identyfikatorze.
     *
     * @param string $id UUID rezerwacji w formacie string
     *
     * @return Reservation|null Rezerwacja, jeśli została znaleziona, null w przeciwnym razie
     */
    public function findById(string $id): ?Reservation;

    /**
     * Zwraca wszystkie rezerwacje w systemie.
     *
     * @return Reservation[] Tablica wszystkich rezerwacji
     */
    public function findAll(): array;

    /**
     * Zwraca wszystkie rezerwacje dla określonego zasobu.
     *
     * @param string $resourceId UUID zasobu w formacie string
     *
     * @return Reservation[] Tablica rezerwacji dla danego zasobu
     */
    public function findByResourceId(string $resourceId): array;

    /**
     * Zwraca wszystkie rezerwacje dla określonego zasobu w danym dniu.
     *
     * @param string $resourceId UUID zasobu w formacie string
     * @param DateTimeImmutable $date Data, dla której mają być zwrócone rezerwacje
     *
     * @return Reservation[] Tablica rezerwacji dla danego zasobu w określonym dniu
     */
    public function findByResourceIdAndDate(string $resourceId, DateTimeImmutable $date): array;
}
