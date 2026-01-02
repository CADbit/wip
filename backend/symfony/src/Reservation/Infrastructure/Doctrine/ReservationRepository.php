<?php

declare(strict_types=1);

namespace App\Reservation\Infrastructure\Doctrine;

use App\Reservation\Domain\Entity\Reservation;
use App\Reservation\Domain\Repository\ReservationRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;

class ReservationRepository extends ServiceEntityRepository implements ReservationRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $entityManager
    )
    {
        parent::__construct($registry, Reservation::class);
    }

    public function save(Reservation $Reservation): void
    {
        $this->entityManager->persist($Reservation);
    }

    public function remove(Reservation $Reservation): void
    {
        $this->entityManager->remove($Reservation);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function findById(string $id): ?Reservation
    {
        return $this->entityManager->find(Reservation::class, $id);
    }

    /** @return Reservation[] */
    public function findAll(): array
    {
        return $this->createQueryBuilder('r')
            ->getQuery()
            ->getResult();
    }
}
