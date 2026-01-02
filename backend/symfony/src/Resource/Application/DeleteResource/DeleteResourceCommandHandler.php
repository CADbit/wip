<?php

declare(strict_types=1);

namespace App\Resource\Application\DeleteResource;

use App\Resource\Domain\Repository\ResourceRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteResourceCommandHandler
{
    public function __construct(
        private readonly ResourceRepositoryInterface $resourceRepository
    ) {
    }

    public function __invoke(DeleteResourceCommand $command): void
    {
        $resource = $this->resourceRepository->findById($command->id->toString());

        if (!$resource) {
            throw new \DomainException('Zasób nie został znaleziony');
        }

        $this->resourceRepository->remove($resource);
        $this->resourceRepository->flush();
    }
}

