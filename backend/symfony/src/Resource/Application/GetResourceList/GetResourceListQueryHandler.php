<?php

declare(strict_types=1);

namespace App\Resource\Application\GetResourceList;

use App\Resource\Domain\Entity\Resource;
use App\Resource\Domain\Enum\ResourceStatus;
use App\Resource\Domain\Repository\ResourceRepositoryInterface;

class GetResourceListQueryHandler
{
    public function __construct(
        private readonly ResourceRepositoryInterface $resourceRepository
    ) {
    }

    /**
     * @return Resource[]
     */
    public function __invoke(GetResourceListQuery $query): array
    {
        if ($query->type && $query->status === ResourceStatus::ACTIVE) {
            return $this->resourceRepository->findAllActiveByType($query->type);
        }

        if ($query->status === ResourceStatus::ACTIVE) {
            return $this->resourceRepository->findAllActive();
        }

        return $this->resourceRepository->findAll();
    }
}

