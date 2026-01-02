<?php

declare(strict_types=1);

namespace App\Resource\Application\UpdateResource;

use App\Resource\Domain\Repository\ResourceRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateResourceCommandHandler
{
    public function __construct(
        private readonly ResourceRepositoryInterface $resourceRepository
    ) {
    }

    public function __invoke(UpdateResourceCommand $command): void
    {
        $resource = $this->resourceRepository->findById($command->id->toString());

        if (! $resource) {
            throw new \DomainException('Zasób nie został znaleziony');
        }

        if ($command->name !== null) {
            $resource->name = $command->name;
        }

        if ($command->description !== null) {
            $resource->description = $command->description;
        }

        if ($command->status !== null) {
            $resource->status = $command->status;
        }

        if ($command->unavailability !== null) {
            $resource->unavailability = $command->unavailability;
        }

        $this->resourceRepository->save($resource);
        $this->resourceRepository->flush();
    }
}
