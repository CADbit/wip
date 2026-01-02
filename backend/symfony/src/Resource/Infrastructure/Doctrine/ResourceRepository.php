<?php

declare(strict_types=1);

namespace App\Resource\Infrastructure\Doctrine;

use App\Resource\Domain\Entity\Resource;
use App\Resource\Domain\Enum\ResourceStatus;
use App\Resource\Domain\Enum\ResourceType;
use App\Resource\Domain\Repository\ResourceRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;

class ResourceRepository extends ServiceEntityRepository implements ResourceRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly EntityManagerInterface $entityManager
    )
    {
        parent::__construct($registry, Resource::class);
    }

    public function save(Resource $resource): void
    {
        $this->entityManager->persist($resource);
    }

    public function remove(Resource $resource): void
    {
        $this->entityManager->remove($resource);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function findById(string $id): ?Resource
    {
        return $this->entityManager->find(Resource::class, $id);
    }

    /** @return Resource[] */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.status = :status')
            ->setParameter('status', ResourceStatus::ACTIVE)
            ->getQuery()
            ->getArrayResult();
    }

    /** @return Resource[] */
    public function findAllActiveByType(ResourceType $type = ResourceType::CONFERENCE_ROOM): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.status = :status')
            ->andWhere('r.type = :type')
            ->setParameter('status', ResourceStatus::ACTIVE)
            ->setParameter('type', $type)
            ->getQuery()
            ->getArrayResult();
    }
}
