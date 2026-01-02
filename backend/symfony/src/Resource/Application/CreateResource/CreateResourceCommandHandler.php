<?php

declare(strict_types=1);

namespace App\Resource\Application\CreateResource;

use App\Resource\Domain\Entity\Resource;
use App\Resource\Domain\Repository\ResourceRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateResourceCommandHandler
{
    public function __construct(
        private readonly ResourceRepositoryInterface $resourceRepository
    ) {
    }

    public function __invoke(CreateResourceCommand $command): void
    {
        $resource = new Resource(
            id: $command->id,
            type: $command->type,
            name: $command->name,
            description: $command->description,
            status: $command->status,
            unavailability: $command->unavailability
        );

        $this->resourceRepository->save($resource);
        $this->resourceRepository->flush();
    }
}
