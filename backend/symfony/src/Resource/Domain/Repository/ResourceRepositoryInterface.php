<?php

declare(strict_types=1);

namespace App\Resource\Domain\Repository;

use App\Resource\Domain\Entity\Resource;
use App\Resource\Domain\Enum\ResourceType;
use App\Resource\Infrastructure\Doctrine\ResourceRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

/**
 * Interfejs repozytorium dla zasobów (sal konferencyjnych).
 *
 * Definiuje kontrakt dla operacji CRUD na zasobach w systemie.
 * Implementacja powinna zapewniać trwałość danych w bazie danych.
 *
 * @see ResourceRepository
 */
interface ResourceRepositoryInterface
{
    /**
     * Zapisuje zasób do repozytorium (bez zapisu do bazy).
     *
     * @param Resource $resource Zasób do zapisania
     */
    public function save(Resource $resource): void;

    /**
     * Oznacza zasób do usunięcia (bez usunięcia z bazy).
     *
     * @param Resource $resource Zasób do usunięcia
     */
    public function remove(Resource $resource): void;

    /**
     * Zapisuje wszystkie oczekujące zmiany do bazy danych.
     *
     * @throws ORMException Jeśli wystąpi błąd podczas zapisu
     * @throws OptimisticLockException Jeśli wystąpi konflikt wersji
     */
    public function flush(): void;

    /**
     * Znajduje zasób po jego identyfikatorze.
     *
     * @param string $id UUID zasobu w formacie string
     *
     * @return Resource|null Zasób, jeśli został znaleziony, null w przeciwnym razie
     */
    public function findById(string $id): ?Resource;

    /**
     * Zwraca wszystkie zasoby w systemie.
     *
     * @return Resource[] Tablica wszystkich zasobów (aktywnych i nieaktywnych)
     */
    public function findAll(): array;

    /**
     * Zwraca wszystkie aktywne zasoby w systemie.
     *
     * @return Resource[] Tablica aktywnych zasobów (status = active)
     */
    public function findAllActive(): array;

    /**
     * Zwraca wszystkie aktywne zasoby określonego typu.
     *
     * @param ResourceType $type Typ zasobu do filtrowania (domyślnie CONFERENCE_ROOM)
     *
     * @return Resource[] Tablica aktywnych zasobów danego typu
     */
    public function findAllActiveByType(ResourceType $type = ResourceType::CONFERENCE_ROOM): array;
}
