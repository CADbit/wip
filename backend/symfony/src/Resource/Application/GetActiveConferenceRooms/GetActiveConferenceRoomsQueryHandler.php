<?php

declare(strict_types=1);

namespace App\Resource\Application\GetActiveConferenceRooms;

use App\Resource\Domain\Entity\Resource;
use App\Resource\Domain\Enum\ResourceType;
use App\Resource\Domain\Repository\ResourceRepositoryInterface;

class GetActiveConferenceRoomsQueryHandler
{
    public function __construct(
        private readonly ResourceRepositoryInterface $resourceRepository
    ) {
    }

    /**
     * @return Resource[]
     */
    public function __invoke(GetActiveConferenceRoomsQuery $query): array
    {
        return $this->resourceRepository->findAllActiveByType(ResourceType::CONFERENCE_ROOM);
    }
}

