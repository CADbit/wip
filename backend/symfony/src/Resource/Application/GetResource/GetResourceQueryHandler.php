<?php

declare(strict_types=1);

namespace App\Resource\Application\GetResource;

use App\Resource\Domain\Entity\Resource;
use App\Resource\Domain\Repository\ResourceRepositoryInterface;

class GetResourceQueryHandler
{
    public function __construct(
        private readonly ResourceRepositoryInterface $resourceRepository
    ) {
    }

    public function __invoke(GetResourceQuery $query): ?Resource
    {
        return $this->resourceRepository->findById($query->id->toString());
    }
}
