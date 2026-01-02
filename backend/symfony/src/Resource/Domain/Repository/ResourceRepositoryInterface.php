<?php

declare(strict_types=1);

namespace App\Resource\Domain\Repository;

use App\Resource\Domain\Entity\Resource;
use App\Resource\Domain\Enum\ResourceType;

interface ResourceRepositoryInterface
{
    public function save(Resource $resource): void;
    public function remove(Resource $resource): void;
    public function flush(): void;
    public function findById(string $id): ?Resource;

    /** @return Resource[] */
    public function findAll(): array;

    /** @return Resource[] */
    public function findAllActive(): array;

    /** @return Resource[] */
    public function findAllActiveByType(ResourceType $type = ResourceType::CONFERENCE_ROOM): array;
}
