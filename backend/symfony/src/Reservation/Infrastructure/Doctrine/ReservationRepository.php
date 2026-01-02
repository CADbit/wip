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

/**
 * @extends ServiceEntityRepository<Reservation>
 */
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

    /**
     * @return array<Reservation>
     */
    public function findAll(): array
    {
        /** @var array<Reservation> $result */
        $result = $this->createQueryBuilder('r')
            ->getQuery()
            ->getResult();
        
        return $result;
    }

    /**
     * @return array<Reservation>
     */
    public function findByResourceId(string $resourceId): array
    {
        /** @var array<Reservation> $result */
        $result = $this->createQueryBuilder('r')
            ->join('r.resource', 'res')
            ->where('res.id = :resourceId')
            ->setParameter('resourceId', $resourceId)
            ->getQuery()
            ->getResult();
        
        return $result;
    }

    /**
     * @return array<Reservation>
     */
    public function findByResourceIdAndDate(string $resourceId, \DateTimeImmutable $date): array
    {
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59, 999999);

        $startFormatted = $startOfDay->format('Y-m-d H:i:s.uP');
        $endFormatted = $endOfDay->format('Y-m-d H:i:s.uP');
        $dayRange = sprintf('[%s,%s)', $startFormatted, $endFormatted);

        $allReservations = $this->findByResourceId($resourceId);
        
        $filteredReservations = array_filter($allReservations, function (Reservation $reservation) use ($startOfDay, $endOfDay) {
            $reservationStart = $reservation->period->start();
            $reservationEnd = $reservation->period->end();
            
            return $reservationStart < $endOfDay && $reservationEnd > $startOfDay;
        });

        usort($filteredReservations, function (Reservation $a, Reservation $b) {
            return $a->period->start() <=> $b->period->start();
        });

        return array_values($filteredReservations);
    }
}
